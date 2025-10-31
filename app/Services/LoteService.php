<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\ProductoUbicacion;
use App\Models\LoteMovimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LoteService
{
    /**
     * Crear un nuevo lote al recibir mercadería
     */
    public function crearLote(array $datos): ProductoUbicacion
    {
        return DB::transaction(function () use ($datos) {
            // Obtener ubicación por defecto si no se proporciona una
            $ubicacionId = $datos['ubicacion_id'] ?? $this->obtenerUbicacionPorDefecto();
            
            // Crear payload compatible con distintos esquemas
            $payload = [
                'producto_id' => $datos['producto_id'],
                'ubicacion_id' => $ubicacionId,
                'cantidad' => $datos['cantidad'],
                'fecha_ingreso' => now(),
                'fecha_vencimiento' => $datos['fecha_vencimiento'] ?? null,
                'lote' => $datos['lote'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null
            ];

            // Campos adicionales si existen en la tabla
            if (Schema::hasColumn('producto_ubicaciones', 'cantidad_inicial')) {
                $payload['cantidad_inicial'] = $datos['cantidad'];
            }
            if (Schema::hasColumn('producto_ubicaciones', 'cantidad_vendida')) {
                $payload['cantidad_vendida'] = 0;
            }
            if (Schema::hasColumn('producto_ubicaciones', 'precio_compra_lote')) {
                $payload['precio_compra_lote'] = $datos['precio_compra'] ?? null;
            }
            if (Schema::hasColumn('producto_ubicaciones', 'precio_venta_lote')) {
                $payload['precio_venta_lote'] = $datos['precio_venta'] ?? null;
            }
            if (Schema::hasColumn('producto_ubicaciones', 'proveedor_id')) {
                $payload['proveedor_id'] = $datos['proveedor_id'] ?? null;
            }
            if (Schema::hasColumn('producto_ubicaciones', 'estado_lote')) {
                $payload['estado_lote'] = 'activo';
            }

            // Crear el lote
            $lote = ProductoUbicacion::create($payload);

            // Registrar movimiento
            $this->registrarMovimiento($lote, 'entrada', $datos['cantidad'], 0, $datos['cantidad'], 'Entrada de mercadería');

            // Actualizar stock total del producto
            $this->actualizarStockProducto($datos['producto_id']);

            Log::info("Nuevo lote creado", [
                'lote_id' => $lote->id,
                'producto_id' => $datos['producto_id'],
                'cantidad' => $datos['cantidad'],
                'lote' => $datos['lote']
            ]);

            return $lote;
        });
    }

    /**
     * Obtener lotes disponibles para venta (FIFO)
     */
    public function obtenerLotesParaVenta(int $productoId, int $cantidadRequerida): array
    {
        $lotes = ProductoUbicacion::where('producto_id', $productoId)
            ->where('estado_lote', 'activo')
            ->where('cantidad', '>', 0)
            ->orderBy('fecha_vencimiento', 'asc') // FIFO: primero los que vencen antes
            ->orderBy('fecha_ingreso', 'asc')    // En caso de misma fecha, primero los más antiguos
            ->get();

        $lotesParaVenta = [];
        $cantidadRestante = $cantidadRequerida;

        foreach ($lotes as $lote) {
            if ($cantidadRestante <= 0) break;

            $cantidadDelLote = min($cantidadRestante, $lote->cantidad);
            
            $lotesParaVenta[] = [
                'lote' => $lote,
                'cantidad_a_usar' => $cantidadDelLote,
                'precio_venta' => $lote->precio_venta_lote ?? $lote->producto->precio_venta
            ];

            $cantidadRestante -= $cantidadDelLote;
        }

        return [
            'lotes' => $lotesParaVenta,
            'cantidad_disponible' => $cantidadRequerida - $cantidadRestante,
            'cantidad_faltante' => $cantidadRestante
        ];
    }

    /**
     * Procesar venta usando lógica FIFO
     */
    public function procesarVenta(int $productoId, int $cantidad, array $datosVenta = []): array
    {
        return DB::transaction(function () use ($productoId, $cantidad, $datosVenta) {
            $resultado = $this->obtenerLotesParaVenta($productoId, $cantidad);
            
            if ($resultado['cantidad_faltante'] > 0) {
                throw new \Exception("Stock insuficiente. Disponible: {$resultado['cantidad_disponible']}, Requerido: {$cantidad}");
            }

            $lotesUsados = [];
            
            foreach ($resultado['lotes'] as $loteInfo) {
                $lote = $loteInfo['lote'];
                $cantidadUsada = $loteInfo['cantidad_a_usar'];
                
                // Actualizar cantidades del lote
                $cantidadAnterior = $lote->cantidad;
                $cantidadNueva = $cantidadAnterior - $cantidadUsada;
                $cantidadVendidaNueva = $lote->cantidad_vendida + $cantidadUsada;
                
                $lote->update([
                    'cantidad' => $cantidadNueva,
                    'cantidad_vendida' => $cantidadVendidaNueva,
                    'estado_lote' => $cantidadNueva <= 0 ? 'agotado' : 'activo'
                ]);

                // Registrar movimiento
                $this->registrarMovimiento(
                    $lote, 
                    'venta', 
                    $cantidadUsada, 
                    $cantidadAnterior, 
                    $cantidadNueva,
                    'Venta de producto',
                    $datosVenta
                );

                $lotesUsados[] = [
                    'lote_id' => $lote->id,
                    'lote_codigo' => $lote->lote,
                    'cantidad_usada' => $cantidadUsada,
                    'precio_venta' => $loteInfo['precio_venta'],
                    'fecha_vencimiento' => $lote->fecha_vencimiento
                ];
            }

            // Actualizar stock total del producto
            $this->actualizarStockProducto($productoId);

            return $lotesUsados;
        });
    }

    /**
     * Obtener información de lotes de un producto
     */
    public function obtenerInfoLotes(int $productoId): array
    {
        $lotes = ProductoUbicacion::where('producto_id', $productoId)
            ->where('cantidad', '>', 0)
            ->orderBy('fecha_vencimiento', 'asc')
            ->orderBy('fecha_ingreso', 'asc')
            ->get();

        $stockTotal = $lotes->sum('cantidad');
        $proximoVencer = $lotes->where('fecha_vencimiento', '<=', now()->addDays(30))->sum('cantidad');
        $vencidos = $lotes->where('fecha_vencimiento', '<', now())->sum('cantidad');

        return [
            'stock_total' => $stockTotal,
            'total_lotes' => $lotes->count(),
            'proximo_vencer' => $proximoVencer,
            'vencidos' => $vencidos,
            'lotes' => $lotes->map(function ($lote) {
                return [
                    'id' => $lote->id,
                    'lote' => $lote->lote,
                    'cantidad' => $lote->cantidad,
                    'cantidad_inicial' => $lote->cantidad_inicial,
                    'cantidad_vendida' => $lote->cantidad_vendida,
                    'fecha_ingreso' => $lote->fecha_ingreso,
                    'fecha_vencimiento' => $lote->fecha_vencimiento,
                    'dias_para_vencer' => $lote->fecha_vencimiento ? now()->diffInDays($lote->fecha_vencimiento, false) : null,
                    'estado' => $this->determinarEstadoLote($lote),
                    'precio_compra' => $lote->precio_compra_lote,
                    'precio_venta' => $lote->precio_venta_lote,
                    'ubicacion' => $lote->ubicacion ? $lote->ubicacion->codigo : null
                ];
            })
        ];
    }

    /**
     * Determinar el estado de un lote
     */
    private function determinarEstadoLote(ProductoUbicacion $lote): string
    {
        if ($lote->cantidad <= 0) {
            return 'agotado';
        }

        if ($lote->fecha_vencimiento) {
            $diasParaVencer = now()->diffInDays($lote->fecha_vencimiento, false);
            
            if ($diasParaVencer < 0) {
                return 'vencido';
            } elseif ($diasParaVencer <= 30) {
                return 'por_vencer';
            }
        }

        return 'normal';
    }

    /**
     * Marcar lotes vencidos
     */
    public function marcarLotesVencidos(): int
    {
        $lotesVencidos = ProductoUbicacion::where('fecha_vencimiento', '<', now())
            ->where('estado_lote', '!=', 'vencido')
            ->get();

        $contador = 0;
        foreach ($lotesVencidos as $lote) {
            $lote->update(['estado_lote' => 'vencido']);
            
            $this->registrarMovimiento(
                $lote, 
                'vencimiento', 
                0, 
                0, 
                0,
                'Lote marcado como vencido automáticamente'
            );
            
            $contador++;
        }

        if ($contador > 0) {
            Log::info("Marcados {$contador} lotes como vencidos");
        }

        return $contador;
    }

    /**
     * Actualizar stock total del producto basado en sus lotes
     */
    private function actualizarStockProducto(int $productoId): void
    {
        $query = ProductoUbicacion::where('producto_id', $productoId);
        if (Schema::hasColumn('producto_ubicaciones', 'estado_lote')) {
            $query->where('estado_lote', 'activo');
        }
        $stockEnLotes = $query->sum('cantidad');

        $producto = Producto::find($productoId);
        if ($producto) {
            // Solo actualizar si el stock en lotes es diferente al stock actual
            // Esto evita sobrescribir el stock cuando se hace una entrada de mercadería
            // que aún no tiene lotes ubicados
            if ($stockEnLotes > 0) {
                // Si hay lotes ubicados, el stock total debe ser al menos la suma de los lotes
                // pero puede ser mayor si hay stock sin ubicar
                if ($stockEnLotes > $producto->stock_actual) {
                    $producto->update(['stock_actual' => $stockEnLotes]);
                }
            }
            // Recalcular el estado del producto después de actualizar el stock
            $producto->fresh()->recalcularEstado();
        }
    }

    /**
     * Registrar movimiento de lote
     */
    private function registrarMovimiento(
        ProductoUbicacion $lote, 
        string $tipo, 
        int $cantidad, 
        int $cantidadAnterior = 0, 
        int $cantidadNueva = 0, 
        string $motivo = null,
        array $datosAdicionales = []
    ): void {
        // Usar solo las columnas que existen en la tabla lote_movimientos
        $observaciones = $motivo;
        if (!empty($datosAdicionales)) {
            $observaciones .= ' - Datos: ' . json_encode($datosAdicionales);
        }

        if (Schema::hasTable('lote_movimientos')) {
            LoteMovimiento::create([
                'producto_ubicacion_id' => $lote->id,
                'tipo_movimiento' => $tipo,
                'cantidad' => $cantidad,
                'precio_unitario' => $lote->precio_venta_lote ?? null,
                'observaciones' => $observaciones,
                'usuario_id' => auth()->id()
            ]);
        } else {
            Log::warning('Tabla lote_movimientos no existe; se omite registro de movimiento');
        }
    }

    /**
     * Obtener próximos a vencer para alertas
     */
    public function obtenerProximosAVencer(int $dias = 30): array
    {
        $lotes = ProductoUbicacion::with(['producto', 'ubicacion'])
            ->where('fecha_vencimiento', '<=', now()->addDays($dias))
            ->where('fecha_vencimiento', '>', now())
            ->where('cantidad', '>', 0)
            ->where('estado_lote', 'activo')
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        return $lotes->map(function ($lote) {
            return [
                'producto_nombre' => $lote->producto->nombre,
                'lote' => $lote->lote,
                'cantidad' => $lote->cantidad,
                'fecha_vencimiento' => $lote->fecha_vencimiento,
                'dias_para_vencer' => now()->diffInDays($lote->fecha_vencimiento, false),
                'ubicacion' => $lote->ubicacion ? $lote->ubicacion->codigo : 'Sin ubicar'
            ];
        })->toArray();
    }

    /**
     * Obtener ubicación por defecto cuando no se especifica una
     */
    private function obtenerUbicacionPorDefecto(): int
    {
        // Logging para diagnóstico
        Log::info('Iniciando búsqueda de ubicación por defecto');
        
        // Buscar una ubicación por defecto o usar la primera disponible
        $ubicacion = DB::table('ubicaciones')
            ->where('activo', 1)
            ->orderBy('id')
            ->first();

        Log::info('Resultado de búsqueda de ubicación', [
            'ubicacion_encontrada' => $ubicacion ? true : false,
            'ubicacion_id' => $ubicacion ? $ubicacion->id : null,
            'total_ubicaciones' => DB::table('ubicaciones')->count(),
            'ubicaciones_activas' => DB::table('ubicaciones')->where('activo', 1)->count()
        ]);

        if (!$ubicacion) {
            Log::error('No se encontraron ubicaciones activas', [
                'todas_ubicaciones' => DB::table('ubicaciones')->get()->toArray()
            ]);
            throw new \Exception('No hay ubicaciones disponibles en el sistema');
        }

        Log::info('Ubicación por defecto seleccionada', ['ubicacion_id' => $ubicacion->id]);
        return $ubicacion->id;
    }

    /**
     * Simular una venta para mostrar qué lotes se utilizarían
     */
    public function simularVenta(int $productoId, int $cantidad): array
    {
        $resultado = $this->obtenerLotesParaVenta($productoId, $cantidad);
        
        if (empty($resultado)) {
            return [
                'success' => false,
                'message' => 'No hay lotes disponibles para este producto',
                'lotes_utilizados' => [],
                'cantidad_total' => 0,
                'precio_total' => 0
            ];
        }
        
        $lotesUtilizados = [];
        $cantidadRestante = $cantidad;
        $precioTotal = 0;
        
        foreach ($resultado as $lote) {
            if ($cantidadRestante <= 0) break;
            
            $cantidadDelLote = min($cantidadRestante, $lote['cantidad']);
            $precioLote = $cantidadDelLote * $lote['precio_venta_lote'];
            
            $lotesUtilizados[] = [
                'lote_id' => $lote['id'],
                'lote_codigo' => $lote['lote'],
                'cantidad_usada' => $cantidadDelLote,
                'precio_unitario' => $lote['precio_venta_lote'],
                'precio_total_lote' => $precioLote,
                'fecha_vencimiento' => $lote['fecha_vencimiento'],
                'fecha_ingreso' => $lote['fecha_ingreso']
            ];
            
            $precioTotal += $precioLote;
            $cantidadRestante -= $cantidadDelLote;
        }
        
        return [
            'success' => true,
            'lotes_utilizados' => $lotesUtilizados,
            'cantidad_total' => $cantidad - $cantidadRestante,
            'precio_total' => $precioTotal,
            'precio_promedio' => $cantidad > 0 ? $precioTotal / $cantidad : 0,
            'cantidad_faltante' => $cantidadRestante
        ];
    }
}