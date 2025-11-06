<?php
namespace App\Http\Controllers\Venta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PuntoVenta\Venta;
use App\Models\PuntoVenta\VentaDetalle;
use App\Models\PuntoVenta\VentaDevolucion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentaController extends Controller
{
    public function nueva()
    {
        return view('ventas.nueva');
    }

    public function historial(Request $request)
    {
        // Estadísticas
        $estadisticas = $this->obtenerEstadisticas();
        
        // Query base - cargar devoluciones para el historial
        $query = Venta::with(['usuario', 'detalles.producto', 'devoluciones'])
            ->orderBy('fecha_venta', 'desc');
        
        // Filtros
        if ($request->filled('metodo_pago')) {
            $query->where('metodo_pago', $request->metodo_pago);
        }
        
        if ($request->filled('tipo_comprobante')) {
            $query->where('tipo_comprobante', $request->tipo_comprobante);
        }
        
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_desde);
        }
        
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_venta', '<=', $request->fecha_hasta);
        }
        
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_venta', 'like', "%{$search}%")
                  ->orWhere('cliente_razon_social', 'like', "%{$search}%")
                  ->orWhere('cliente_numero_documento', 'like', "%{$search}%")
                  ->orWhereHas('detalles.producto', function($productQuery) use ($search) {
                      $productQuery->where('nombre', 'like', "%{$search}%");
                  });
            });
        }
        
        // Paginación
        $ventas = $query->paginate(10);
        
        // Obtener usuarios para filtro
        $usuarios = User::orderBy('name')->get();
        
        return view('ventas.historial', compact('ventas', 'estadisticas', 'usuarios'));
    }

    public function devoluciones(Request $request)
    {
        // Estadísticas de devoluciones
        $estadisticas = $this->obtenerEstadisticasDevoluciones();
        
        // Si hay búsqueda de venta
        $venta = null;
        if ($request->filled('numero_venta')) {
            // Primero verificar si la venta existe
            $ventaExiste = Venta::where('numero_venta', $request->numero_venta)->first();
            
            \Log::info('Búsqueda de venta para devolución:', [
                'numero_venta' => $request->numero_venta,
                'venta_existe' => $ventaExiste ? 'SÍ' : 'NO',
                'estado_actual' => $ventaExiste ? $ventaExiste->estado : 'N/A'
            ]);
            
            if ($ventaExiste) {
                // Buscar ventas que pueden tener devoluciones (todas excepto canceladas)
                $venta = Venta::with(['detalles.producto', 'usuario', 'devoluciones'])
                    ->where('numero_venta', $request->numero_venta)
                    ->whereNotIn('estado', ['cancelada'])
                    ->first();
                    
                \Log::info('Resultado de búsqueda:', [
                    'venta_encontrada_para_devolucion' => $venta ? 'SÍ' : 'NO',
                    'estado_venta' => $venta ? $venta->estado : 'NO ENCONTRADA',
                    'numero_detalles' => $venta ? $venta->detalles->count() : 0,
                    'numero_devoluciones' => $venta ? $venta->devoluciones->count() : 0
                ]);
            }
        }
        
        return view('ventas.devoluciones', compact('estadisticas', 'venta'));
    }

    public function procesarDevolucion(Request $request)
    {
        // Validación mejorada con mensajes personalizados
        try {
            $request->validate([
                'venta_id' => 'required|exists:ventas,id',
                'productos' => 'required|array|min:1',
                'productos.*.detalle_id' => 'required|exists:venta_detalles,id',
                'productos.*.cantidad_devolver' => 'required|integer|min:1',
                'productos.*.motivo' => 'required|string|in:defectuoso,vencido,equivocacion,cliente_insatisfecho,cambio_opinion,otro',
                'productos.*.observaciones' => 'nullable|string|max:500',
            ], [
                'venta_id.required' => 'El ID de venta es requerido',
                'venta_id.exists' => 'La venta especificada no existe',
                'productos.required' => 'Debe seleccionar al menos un producto para devolver',
                'productos.array' => 'Los productos deben ser un array válido',
                'productos.min' => 'Debe seleccionar al menos un producto para devolver',
                'productos.*.detalle_id.required' => 'El ID del detalle del producto es requerido',
                'productos.*.detalle_id.exists' => 'El detalle del producto no existe',
                'productos.*.cantidad_devolver.required' => 'La cantidad a devolver es requerida',
                'productos.*.cantidad_devolver.integer' => 'La cantidad debe ser un número entero',
                'productos.*.cantidad_devolver.min' => 'La cantidad a devolver debe ser mayor a 0',
                'productos.*.motivo.required' => 'El motivo de devolución es requerido',
                'productos.*.motivo.in' => 'El motivo seleccionado no es válido',
                'productos.*.observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación en devolución:', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'usuario_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error de validación. Revise los datos enviados.',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $venta = Venta::with(['detalles.producto', 'devoluciones'])->findOrFail($request->venta_id);
            
            \Log::info('Procesando devolución:', [
                'venta_id' => $venta->id,
                'numero_venta' => $venta->numero_venta,
                'estado_actual' => $venta->estado,
                'fecha_venta' => $venta->fecha_venta,
                'total_productos_request' => count($request->productos)
            ]);
            
            // Verificar que la venta puede tener devoluciones
            if (in_array($venta->estado, ['cancelada'])) {
                \Log::warning('Intento de devolución en venta cancelada:', [
                    'venta_id' => $venta->id,
                    'numero_venta' => $venta->numero_venta,
                    'estado' => $venta->estado
                ]);
                throw new \Exception("No se pueden procesar devoluciones de ventas canceladas. Estado actual: {$venta->estado}");
            }
            
            $devolucionesCreadas = [];
            $totalDevolucion = 0;
            $productosDevueltos = 0;

            foreach ($request->productos as $productoData) {
                $detalle = VentaDetalle::with('producto')->findOrFail($productoData['detalle_id']);
                
                // Validar que pertenece a la venta
                if ($detalle->venta_id != $venta->id) {
                    throw new \Exception("El producto no pertenece a esta venta");
                }
                
                // Calcular cuánto se ha devuelto previamente de este detalle
                $cantidadPreviamenteDevuelta = $venta->devoluciones()
                    ->where('venta_detalle_id', $detalle->id)
                    ->sum('cantidad_devuelta');
                
                $cantidadRestante = $detalle->cantidad - $cantidadPreviamenteDevuelta;
                
                \Log::info("Validación de cantidades:", [
                    'producto' => $detalle->producto->nombre,
                    'detalle_id' => $detalle->id,
                    'cantidad_original' => $detalle->cantidad,
                    'cantidad_previamente_devuelta' => $cantidadPreviamenteDevuelta,
                    'cantidad_restante' => $cantidadRestante,
                    'cantidad_a_devolver' => $productoData['cantidad_devolver']
                ]);
                
                // Validar que no devuelva más de lo que queda disponible
                if ($productoData['cantidad_devolver'] > $cantidadRestante) {
                    throw new \Exception("No puedes devolver {$productoData['cantidad_devolver']} unidades de {$detalle->producto->nombre}. Solo quedan {$cantidadRestante} unidades disponibles para devolución.");
                }

                // Calcular monto de devolución
                $montoDevolucion = $productoData['cantidad_devolver'] * $detalle->precio_unitario;
                $totalDevolucion += $montoDevolucion;
                $productosDevueltos++;

                // Crear registro de devolución
                $devolucion = \App\Models\PuntoVenta\VentaDevolucion::create([
                    'venta_id' => $venta->id,
                    'venta_detalle_id' => $detalle->id,
                    'producto_id' => $detalle->producto_id,
                    'usuario_id' => auth()->id(),
                    'cantidad_original' => $detalle->cantidad,
                    'cantidad_devuelta' => $productoData['cantidad_devolver'],
                    'precio_unitario' => $detalle->precio_unitario,
                    'monto_devolucion' => $montoDevolucion,
                    'motivo' => $productoData['motivo'],
                    'observaciones' => $productoData['observaciones'] ?? null,
                    'fecha_devolucion' => now()
                ]);

                $devolucionesCreadas[] = $devolucion;

                // Actualizar stock del producto (devolver al inventario)
                $detalle->producto->increment('stock_actual', $productoData['cantidad_devolver']);
                
                // Recalcular el estado del producto después de actualizar el stock
                $detalle->producto->fresh()->recalcularEstado();

                // Crear registro de movimiento de stock
                \App\Models\MovimientoStock::registrarDevolucion(
                    $detalle->producto_id,
                    null, // ubicación_id - null porque es devolución general al inventario
                    $productoData['cantidad_devolver'],
                    "Devolución: " . ($productoData['observaciones'] ?? $productoData['motivo']),
                    [
                        'venta_id' => $venta->id,
                        'venta_detalle_id' => $detalle->id,
                        'stock_anterior' => $detalle->producto->stock_actual - $productoData['cantidad_devolver'],
                        'stock_nuevo' => $detalle->producto->stock_actual
                    ]
                );

                \Log::info("Devolución creada: {$productoData['cantidad_devolver']} unidades de {$detalle->producto->nombre}");
            }

            // Actualizar el estado de la venta basado en las devoluciones
            \Log::info("Verificando estado de devolución para venta {$venta->numero_venta}");
            $esDevueltaCompleta = $venta->verificarSiDevueltaCompleta();
            
            // Actualizar totales después de las devoluciones
            \Log::info("Actualizando totales para venta {$venta->numero_venta}");
            $totalesActualizados = $venta->actualizarTotales();
            
            \Log::info("Estado final de venta después de devolución:", [
                'numero_venta' => $venta->numero_venta,
                'estado_final' => $venta->estado,
                'es_devuelta_completa' => $esDevueltaCompleta,
                'total_original' => $venta->total,
                'total_actual' => $venta->total_actual,
                'monto_devuelto' => $venta->monto_total_devuelto
            ]);

            DB::commit();

            // Obtener información actualizada para la respuesta
            $venta->refresh();
            
            // Obtener resumen de devolución con manejo de errores
            try {
                $resumenDevolucion = $venta->resumen_devolucion;
                \Log::info("Resumen de devolución obtenido exitosamente", ['resumen' => $resumenDevolucion]);
            } catch (\Exception $resumenError) {
                \Log::error("Error al obtener resumen de devolución: " . $resumenError->getMessage());
                $resumenDevolucion = [
                    'tiene_devoluciones' => true,
                    'monto_devuelto' => $totalDevolucion,
                    'productos_con_devolucion' => $productosDevueltos,
                    'total_productos_originales' => $venta->detalles->count(),
                    'detalles_afectados' => []
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Devolución procesada exitosamente',
                'data' => [
                    'venta_id' => $venta->id,
                    'nuevo_estado' => $venta->estado,
                    'nuevo_estado_formateado' => $venta->estado_formateado,
                    'total_devolucion_actual' => (float) $totalDevolucion,
                    'productos_devueltos_ahora' => (int) $productosDevueltos,
                    'devoluciones_creadas' => count($devolucionesCreadas),
                    'resumen_general' => $resumenDevolucion,
                    'mensaje_estado' => $this->generarMensajeEstadoSeguro($venta),
                    'totales_actualizados' => $totalesActualizados,
                    'total_original' => (float) $venta->total,
                    'total_actual' => (float) $venta->total_actual,
                    'monto_total_devuelto' => (float) $venta->monto_total_devuelto
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error en devolución: ' . $e->getMessage(), [
                'venta_id' => $request->venta_id,
                'productos' => $request->productos,
                'usuario_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Determinar el código de error apropiado
            $statusCode = 422;
            $errorType = 'business_logic';
            
            if (strpos($e->getMessage(), 'No se pueden procesar devoluciones') !== false) {
                $errorType = 'invalid_state';
            } elseif (strpos($e->getMessage(), 'No puedes devolver') !== false) {
                $errorType = 'quantity_exceeded';
            } elseif (strpos($e->getMessage(), 'no pertenece a esta venta') !== false) {
                $errorType = 'invalid_product';
            }
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_type' => $errorType,
                'errors' => [
                    'general' => [$e->getMessage()]
                ]
            ], $statusCode);
        }
    }

    private function generarMensajeEstadoSeguro($venta)
    {
        try {
            return $this->generarMensajeEstado($venta);
        } catch (\Exception $e) {
            \Log::error("Error al generar mensaje de estado: " . $e->getMessage());
            return "Devolución procesada exitosamente. Estado: {$venta->estado}";
        }
    }

    private function generarMensajeEstado($venta)
    {
        if ($venta->estado === 'devuelta') {
            return "La venta ha sido devuelta completamente. Todos los productos han sido devueltos al inventario.";
        } elseif ($venta->estado === 'parcialmente_devuelta') {
            $resumen = $venta->resumen_devolucion;
            $productosConDevolucion = $resumen['productos_con_devolucion'];
            $totalProductos = $resumen['total_productos_originales'];
            $montoDevuelto = number_format($resumen['monto_devuelto'], 2);
            
            return "La venta tiene devolución parcial: {$productosConDevolucion} de {$totalProductos} productos han sido devueltos. Monto devuelto: S/ {$montoDevuelto}.";
        } else {
            return "La venta se mantiene completada.";
        }
    }

    public function reportes(Request $request)
    {
        $periodo = $request->get('periodo', 'mes'); // día, semana, mes, año
        
        // Obtener datos según el período
        $datos = $this->obtenerDatosReporte($periodo);
        
        return view('ventas.reportes', compact('datos', 'periodo'));
    }
    
    /**
     * API: Obtener datos del reporte en formato JSON
     */
    public function obtenerDatosReporteAPI(Request $request)
    {
        try {
            $periodo = $request->get('periodo', 'mes');
            
            // Obtener datos según el período
            $datos = $this->obtenerDatosReporte($periodo);
            
            return response()->json($datos);
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo datos del reporte: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al obtener datos del reporte',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    private function obtenerEstadisticas()
    {
        $hoy = Carbon::today();
        $ayer = Carbon::yesterday();
        $inicioMes = Carbon::now()->startOfMonth();
        $mesAnterior = Carbon::now()->subMonth()->startOfMonth();
        $finMesAnterior = Carbon::now()->subMonth()->endOfMonth();
        
        // Ventas de hoy (solo completadas, no devueltas)
        $ventasHoy = Venta::activas()
            ->whereDate('fecha_venta', $hoy)
            ->count();
            
        $ventasAyer = Venta::activas()
            ->whereDate('fecha_venta', $ayer)
            ->count();
        
        // Ventas del mes (solo completadas, no devueltas)
        $ventasMes = Venta::activas()
            ->whereBetween('fecha_venta', [$inicioMes, now()])
            ->count();
            
        $ventasMesAnterior = Venta::activas()
            ->whereBetween('fecha_venta', [$mesAnterior, $finMesAnterior])
            ->count();
        
        // Total de productos vendidos hoy (solo ventas activas)
        $productosVendidosHoy = VentaDetalle::whereHas('venta', function($q) use ($hoy) {
            $q->activas()
              ->whereDate('fecha_venta', $hoy);
        })->sum('cantidad');
        
        // Ingresos de hoy (solo ventas activas)
        $ingresosHoy = Venta::activas()
            ->whereDate('fecha_venta', $hoy)
            ->sum('total');
            
        // Ingresos del mes (solo ventas activas)
        $ingresosMes = Venta::activas()
            ->whereBetween('fecha_venta', [$inicioMes, now()])
            ->sum('total');
        
        return [
            'ventas_hoy' => $ventasHoy,
            'cambio_respecto_ayer' => $ventasHoy - $ventasAyer,
            'ventas_mes' => $ventasMes,
            'cambio_mes' => $ventasMes - $ventasMesAnterior,
            'productos_vendidos' => $productosVendidosHoy,
            'ingresos_hoy' => $ingresosHoy,
            'ingresos_mes' => $ingresosMes
        ];
    }

    private function obtenerEstadisticasDevoluciones()
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        
        // Obtener estadísticas reales de devoluciones
        $devolucionesHoy = VentaDevolucion::whereDate('fecha_devolucion', $hoy)->count();
        $devolucionesMes = VentaDevolucion::whereBetween('fecha_devolucion', [$inicioMes, now()])->count();
        $montoDevueltoHoy = VentaDevolucion::whereDate('fecha_devolucion', $hoy)->sum('monto_devolucion');
        $montoDevueltoMes = VentaDevolucion::whereBetween('fecha_devolucion', [$inicioMes, now()])->sum('monto_devolucion');
        
        return [
            'devoluciones_hoy' => $devolucionesHoy,
            'devoluciones_mes' => $devolucionesMes,
            'monto_devuelto_hoy' => $montoDevueltoHoy,
            'monto_devuelto_mes' => $montoDevueltoMes
        ];
    }

    private function obtenerDatosReporte($periodo)
    {
        $fechaInicio = match($periodo) {
            'dia' => Carbon::today(),
            'semana' => Carbon::now()->startOfWeek(),
            'mes' => Carbon::now()->startOfMonth(),
            'año' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth()
        };

        $fechaFin = match($periodo) {
            'dia' => Carbon::today()->endOfDay(),
            'semana' => Carbon::now()->endOfWeek(),
            'mes' => Carbon::now()->endOfMonth(),
            'año' => Carbon::now()->endOfYear(),
            default => Carbon::now()->endOfMonth()
        };

        // Ventas por período (solo activas, no devueltas)
        $ventas = Venta::activas()
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->get();

        // Productos más vendidos (solo de ventas activas)
        $productosMasVendidos = VentaDetalle::select('producto_id', DB::raw('SUM(cantidad) as total_vendido'))
            ->whereHas('venta', function($q) use ($fechaInicio, $fechaFin) {
                $q->activas()
                  ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin]);
            })
            ->with('producto')
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get();

        // Ventas por método de pago (solo ventas activas)
        $ventasPorMetodo = Venta::select('metodo_pago', DB::raw('COUNT(*) as total'))
            ->activas()
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->groupBy('metodo_pago')
            ->get();

        // Ingresos por día (últimos 7 días, solo ventas activas)
        $ingresosPorDia = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $ingresos = Venta::activas()
                ->whereDate('fecha_venta', $fecha)
                ->sum('total');
            
            $ingresosPorDia[] = [
                'fecha' => $fecha->format('d/m'),
                'ingresos' => $ingresos
            ];
        }

        return [
            'total_ventas' => $ventas->count(),
            'total_ingresos' => $ventas->sum('total'),
            'ticket_promedio' => $ventas->count() > 0 ? $ventas->sum('total') / $ventas->count() : 0,
            'productos_mas_vendidos' => $productosMasVendidos,
            'ventas_por_metodo' => $ventasPorMetodo,
            'ingresos_por_dia' => $ingresosPorDia,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];
    }

    /**
     * Obtener detalle de venta para modal con información de devoluciones
     */
    public function obtenerDetalle($id)
    {
        try {
            $venta = Venta::with(['detalles.producto', 'usuario', 'devoluciones.producto', 'devoluciones.usuario'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'venta' => [
                    'id' => $venta->id,
                    'numero_venta' => $venta->numero_venta,
                    'fecha_venta' => $venta->fecha_venta,
                    'created_at' => $venta->created_at,
                    'subtotal' => $venta->subtotal,
                    'igv' => $venta->igv,
                    'total' => $venta->total,
                    'metodo_pago' => $venta->metodo_pago,
                    'tipo_comprobante' => $venta->tipo_comprobante,
                    'efectivo_recibido' => $venta->efectivo_recibido,
                    'vuelto' => $venta->vuelto,
                    'estado' => $venta->estado,
                    'estado_formateado' => $venta->estado_formateado,
                    'usuario' => $venta->usuario,
                    'tiene_devoluciones' => $venta->tiene_devoluciones,
                    'monto_total_devuelto' => $venta->monto_total_devuelto,
                    'productos_afectados_por_devolucion' => $venta->productos_afectados_por_devolucion,
                    // Información de descuentos
                    'tiene_descuento' => $venta->descuento_monto > 0,
                    'descuento_porcentaje' => $venta->descuento_porcentaje,
                    'descuento_monto' => $venta->descuento_monto,
                    'descuento_tipo' => $venta->descuento_tipo,
                    'descuento_razon' => $venta->descuento_razon,
                    'subtotal_original' => $venta->subtotal + $venta->descuento_monto, // Subtotal antes del descuento
                    'detalles' => $venta->detalles_con_devolucion->map(function ($detalle) {
                        return [
                            'id' => $detalle->id,
                            'cantidad' => $detalle->cantidad,
                            'cantidad_devuelta' => $detalle->cantidad_devuelta ?? 0,
                            'cantidad_restante' => $detalle->cantidad_restante ?? $detalle->cantidad,
                            'tiene_devolucion' => $detalle->tiene_devolucion ?? false,
                            'devolucion_completa' => $detalle->devolucion_completa ?? false,
                            'precio_unitario' => $detalle->precio_unitario,
                            'subtotal_original' => $detalle->subtotal,
                            'monto_devuelto' => ($detalle->cantidad_devuelta ?? 0) * $detalle->precio_unitario,
                            'producto' => [
                                'id' => $detalle->producto->id,
                                'nombre' => $detalle->producto->nombre,
                                'concentracion' => $detalle->producto->concentracion
                            ]
                        ];
                    }),
                    'devoluciones' => $venta->devoluciones->map(function ($devolucion) {
                        return [
                            'id' => $devolucion->id,
                            'fecha_devolucion' => $devolucion->fecha_formateada,
                            'cantidad_devuelta' => $devolucion->cantidad_devuelta,
                            'monto_devolucion' => $devolucion->monto_devolucion,
                            'motivo' => $devolucion->motivo,
                            'motivo_formateado' => $devolucion->motivo_formateado,
                            'observaciones' => $devolucion->observaciones,
                            'usuario' => $devolucion->usuario->name,
                            'producto' => [
                                'nombre' => $devolucion->producto->nombre,
                                'concentracion' => $devolucion->producto->concentracion
                            ]
                        ];
                    }),
                    'resumen_devolucion' => $venta->resumen_devolucion
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el detalle de la venta'
            ], 404);
        }
    }
}