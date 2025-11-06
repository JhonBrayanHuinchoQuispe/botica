<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Services\ImageOptimizationService;
use App\Services\QueryOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class InventarioController extends Controller
{
    protected $queryOptimizationService;

    public function __construct(QueryOptimizationService $queryOptimizationService)
    {
        $this->queryOptimizationService = $queryOptimizationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener parámetros de paginación
        $perPage = $request->get('per_page', 10); // Por defecto 10 productos por página
        $search = $request->get('search', '');
        $estado = $request->get('estado', 'todos');
        
        // Validar que per_page sea un valor válido
        $validPerPage = [5, 10, 25, 50, 100];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }
        
        // Usar el servicio de optimización para obtener productos con eager loading
        $query = $this->queryOptimizationService->getProductosOptimizados([
            'search' => $search,
            'estado' => $estado
        ]);
        
        // Aplicar paginación
        $productos = $query->paginate($perPage)->withQueryString();
        
        // Agregar información de ubicaciones a cada producto
        $productos->getCollection()->transform(function ($producto) {
            $ubicacionesConStock = $producto->ubicaciones->where('cantidad', '>', 0);
            $stockEnUbicaciones = $ubicacionesConStock->sum('cantidad');
            $stockSinUbicar = max(0, $producto->stock_actual - $stockEnUbicaciones);
            
            // Agrupar por ubicación física real (mismo estante y código)
            $ubicacionesAgrupadas = $ubicacionesConStock->groupBy(function ($ubicacion) {
                $estante = $ubicacion->ubicacion?->estante;
                return ($estante?->nombre ?? 'Sin asignar') . ' - ' . ($ubicacion->ubicacion?->codigo ?? 'N/A');
            });
            
            // Contar ubicaciones físicas únicas
            $producto->total_ubicaciones = $ubicacionesAgrupadas->count();
            $producto->stock_en_ubicaciones = $stockEnUbicaciones;
            $producto->stock_sin_ubicar = $stockSinUbicar;
            $producto->tiene_stock_sin_ubicar = $stockSinUbicar > 0;
            
            // Determinar estado de ubicación
            if ($stockEnUbicaciones == 0) {
                $producto->estado_ubicacion = 'sin_ubicar';
                $producto->texto_ubicacion = 'Sin ubicar';
            } elseif ($stockSinUbicar > 0) {
                $producto->estado_ubicacion = 'parcialmente_ubicado';
                $producto->texto_ubicacion = 'Parcialmente ubicado';
            } else {
                $producto->estado_ubicacion = 'completamente_ubicado';
                $producto->texto_ubicacion = 'Completamente ubicado';
            }
            
            // Crear detalle de ubicaciones agrupadas
            $producto->ubicaciones_detalle = $ubicacionesAgrupadas->map(function ($ubicacionesEnMismoLugar, $ubicacionCompleta) {
                $primeraUbicacion = $ubicacionesEnMismoLugar->first();
                $estante = $primeraUbicacion->ubicacion?->estante;
                $cantidadTotal = $ubicacionesEnMismoLugar->sum('cantidad');
                
                // Obtener información de lotes
                $lotes = $ubicacionesEnMismoLugar->map(function ($ub) {
                    return [
                        'lote' => $ub->lote,
                        'cantidad' => $ub->cantidad,
                        'fecha_vencimiento' => $ub->fecha_vencimiento
                    ];
                })->toArray();
                
                return [
                    'estante_nombre' => $estante?->nombre ?? 'Sin asignar',
                    'ubicacion_codigo' => $primeraUbicacion->ubicacion?->codigo ?? 'N/A',
                    'cantidad' => $cantidadTotal,
                    'fecha_vencimiento' => $primeraUbicacion->fecha_vencimiento,
                    'lote' => $primeraUbicacion->lote,
                    'ubicacion_completa' => $ubicacionCompleta,
                    'lotes_detalle' => $lotes, // Información detallada de todos los lotes
                    'tiene_multiples_lotes' => $ubicacionesEnMismoLugar->count() > 1
                ];
            })->values();
            
            // NO agregar stock sin ubicar como ubicación - se maneja por separado
            
            return $producto;
        });
        
        $categorias = Categoria::orderBy('nombre')->get();
        $presentaciones = \App\Models\Presentacion::orderBy('nombre')->get();
        $proveedores = \App\Models\Proveedor::where('estado', 'activo')->orderBy('razon_social')->get();
        $title = 'Gestión de Productos';
        $subTitle = 'Lista de Productos';
        
        // Si es una petición AJAX, devolver solo los datos
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $productos,
                'search' => $search,
                'estado' => $estado,
                'perPage' => $perPage
            ]);
        }
        
        // La vista legacy 'inventario.productos' ya no se utiliza; redirigir a Productos Botica
        return redirect()->route('inventario.productos.botica');
    }

    /**
     * Handle AJAX requests for products listing
     */
    public function ajaxIndex(Request $request)
    {
        try {
            // Obtener parámetros de paginación
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $estado = $request->get('estado', 'todos');
            
            // Validar que per_page sea un valor válido
            $validPerPage = [5, 10, 25, 50, 100];
            if (!in_array($perPage, $validPerPage)) {
                $perPage = 10;
            }
            
            // Usar el servicio de optimización para obtener productos con eager loading
            $query = $this->queryOptimizationService->getProductosOptimizados([
                'search' => $search,
                'estado' => $estado
            ]);
            
            // Aplicar paginación
            $productos = $query->paginate($perPage);
            
            // Agregar información de ubicaciones a cada producto
            $productos->getCollection()->transform(function ($producto) {
                $ubicacionesConStock = $producto->ubicaciones->where('cantidad', '>', 0);
                $stockEnUbicaciones = $ubicacionesConStock->sum('cantidad');
                $stockSinUbicar = max(0, $producto->stock_actual - $stockEnUbicaciones);
                
                // Agrupar por ubicación física real (mismo estante y código)
                $ubicacionesAgrupadas = $ubicacionesConStock->groupBy(function ($ubicacion) {
                    $estante = $ubicacion->ubicacion?->estante;
                    return ($estante?->nombre ?? 'Sin asignar') . ' - ' . ($ubicacion->ubicacion?->codigo ?? 'N/A');
                });
                
                // Contar ubicaciones físicas únicas
                $producto->total_ubicaciones = $ubicacionesAgrupadas->count();
                $producto->stock_en_ubicaciones = $stockEnUbicaciones;
                $producto->stock_sin_ubicar = $stockSinUbicar;
                $producto->tiene_stock_sin_ubicar = $stockSinUbicar > 0;
                
                // Determinar estado de ubicación
                if ($producto->stock_actual <= 0) {
                    // Si el stock total es 0, considerar Sin ubicar para UI y normalizar métricas
                    $producto->total_ubicaciones = 0;
                    $producto->stock_en_ubicaciones = 0;
                    $producto->stock_sin_ubicar = 0;
                    $producto->tiene_stock_sin_ubicar = false;
                    $producto->estado_ubicacion = 'sin_ubicar';
                    $producto->texto_ubicacion = 'Sin ubicar';
                } elseif ($stockEnUbicaciones == 0) {
                    $producto->estado_ubicacion = 'sin_ubicar';
                    $producto->texto_ubicacion = 'Sin ubicar';
                } elseif ($stockSinUbicar > 0) {
                    $producto->estado_ubicacion = 'parcialmente_ubicado';
                    $producto->texto_ubicacion = 'Parcialmente ubicado';
                } else {
                    $producto->estado_ubicacion = 'completamente_ubicado';
                    $producto->texto_ubicacion = 'Completamente ubicado';
                }
                
                // Crear detalle de ubicaciones agrupadas
                $producto->ubicaciones_detalle = $ubicacionesAgrupadas->map(function ($ubicacionesEnMismoLugar, $ubicacionCompleta) {
                    $primeraUbicacion = $ubicacionesEnMismoLugar->first();
                    $estante = $primeraUbicacion->ubicacion?->estante;
                    $cantidadTotal = $ubicacionesEnMismoLugar->sum('cantidad');
                    
                    // Obtener información de lotes
                    $lotes = $ubicacionesEnMismoLugar->map(function ($ub) {
                        return [
                            'lote' => $ub->lote,
                            'cantidad' => $ub->cantidad,
                            'fecha_vencimiento' => $ub->fecha_vencimiento
                        ];
                    })->toArray();
                    
                    return [
                        'estante_nombre' => $estante?->nombre ?? 'Sin asignar',
                        'ubicacion_codigo' => $primeraUbicacion->ubicacion?->codigo ?? 'N/A',
                        'cantidad' => $cantidadTotal,
                        'fecha_vencimiento' => $primeraUbicacion->fecha_vencimiento,
                        'lote' => $primeraUbicacion->lote,
                        'ubicacion_completa' => $ubicacionCompleta,
                        'lotes_detalle' => $lotes, // Información detallada de todos los lotes
                        'tiene_multiples_lotes' => $ubicacionesEnMismoLugar->count() > 1
                    ];
                })->values();
                
                // NO agregar stock sin ubicar como ubicación - se maneja por separado
                
                return $producto;
            });
            
            return response()->json($productos);
            
        } catch (\Exception $e) {
            Log::error('Error en ajaxIndex: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $producto = Producto::with('proveedor')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'categoria' => $producto->categoria,
                    'marca' => $producto->marca,
                    'proveedor_id' => $producto->proveedor_id,
                    'proveedor' => $producto->proveedor ? $producto->proveedor->razon_social : null,
                    'presentacion' => $producto->presentacion,
                    'concentracion' => $producto->concentracion,
                    'lote' => $producto->lote,
                    'codigo_barras' => $producto->codigo_barras,
                    'stock_actual' => $producto->stock_actual,
                    'stock_minimo' => $producto->stock_minimo,
                    'precio_compra' => $producto->precio_compra,
                    'precio_venta' => $producto->precio_venta,
                    'fecha_fabricacion' => $producto->fecha_fabricacion_solo_fecha,
                    'fecha_vencimiento' => $producto->fecha_vencimiento_solo_fecha,
                    'ubicacion' => $producto->ubicacion,
                    'ubicacion_almacen' => $producto->ubicacion_almacen,
                    'imagen' => $producto->imagen,
                    'imagen_url' => $producto->imagen_url,
                    'estado' => $producto->estado
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener producto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el producto: ' . $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Iniciando guardado de producto', [
                'datos_recibidos' => $request->all(),
                'headers' => $request->headers->all()
            ]);
            
            DB::beginTransaction();

            Log::info('Iniciando validación de datos');
            $request->validate([
                'nombre' => 'required|string|max:255',
                'codigo_barras' => 'required|string|max:255|unique:productos',
                'lote' => 'required|string|max:255',
                'categoria' => 'required|string|max:255',
                'marca' => 'required|string|max:255',
                'presentacion' => 'required|string|max:255',
                'concentracion' => 'nullable|string|max:255',
                'stock_actual' => 'required|integer|min:0',
                'stock_minimo' => 'required|integer|min:0',
                'fecha_fabricacion' => 'required|date',
                'fecha_vencimiento' => 'required|date|after_or_equal:fecha_fabricacion',
                'precio_compra' => 'required|numeric|min:0',
                'precio_venta' => 'required|numeric|min:0|gt:precio_compra',
                'imagen' => 'nullable|image|max:2048',
                'proveedor_id' => 'required|integer|exists:proveedores,id',
                'ubicacion' => 'nullable|string|max:255'
            ]);
            Log::info('Validación completada exitosamente');

            Log::info('Procesando imagen');
            $imagePath = null;
            if ($request->hasFile('imagen')) {
                $imageOptimizer = new ImageOptimizationService();
                $imagePath = $imageOptimizer->optimizeProductImage($request->file('imagen'));
                Log::info('Imagen optimizada y guardada: ' . $imagePath);
            }

            Log::info('Determinando estado del producto');
            $estado = $this->determinarEstado(
                $request->stock_actual, 
                $request->stock_minimo, 
                $request->fecha_vencimiento
            );
            Log::info('Estado determinado: ' . $estado);

            Log::info('Creando producto en base de datos');
            // Crear el producto usando el método create de Eloquent (auto-incremento)
            $datosProducto = [
                'nombre' => $request->nombre,
                'codigo_barras' => $request->codigo_barras,
                'lote' => $request->lote,
                'categoria' => $request->categoria,
                'marca' => $request->marca,
                'presentacion' => $request->presentacion,
                'concentracion' => $request->concentracion,
                'stock_actual' => $request->stock_actual,
                'stock_minimo' => $request->stock_minimo,
                'ubicacion' => $request->ubicacion ?? null,
                'fecha_fabricacion' => $request->fecha_fabricacion,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'precio_compra' => $request->precio_compra,
                'precio_venta' => $request->precio_venta,
                'imagen' => $imagePath,
                'proveedor_id' => $request->proveedor_id,
                'estado' => $estado
            ];

            Log::info('Datos del producto a guardar', $datosProducto);

            $producto = Producto::create($datosProducto);
            Log::info('Producto creado exitosamente con ID: ' . $producto->id);

            DB::commit();
            Log::info('Transacción confirmada');

            return response()->json([
                'success' => true,
                'message' => 'Producto guardado exitosamente',
                'data' => $producto
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar producto: ' . $e->getMessage());
            Log::error('Detalles del error: ', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $producto = Producto::findOrFail($id);
            
            Log::info("🗑️ Iniciando eliminación del producto ID: {$id} - {$producto->nombre}");
            
            // DESHABILITAR FOREIGN KEY CHECKS TEMPORALMENTE (si el driver lo soporta)
            try { DB::statement('SET FOREIGN_KEY_CHECKS=0;'); } catch (\Exception $fkOff) { Log::info('FK checks no soportados por el driver actual'); }
            
            // 1. Eliminar detalles de ventas (si existe la tabla)
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('venta_detalles')) {
                    $deletedVentaDetalles = DB::table('venta_detalles')->where('producto_id', $id)->delete();
                    if ($deletedVentaDetalles > 0) {
                        Log::info("✅ Eliminados {$deletedVentaDetalles} registros de venta_detalles");
                    }
                }
            } catch (\Exception $e) { Log::info('ℹ️ Tabla venta_detalles no existe o ya está limpia'); }
            
            // 2. Eliminar movimientos de stock (si existe)
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('movimientos_stock')) {
                    $deletedMovimientos = DB::table('movimientos_stock')->where('producto_id', $id)->delete();
                    if ($deletedMovimientos > 0) { Log::info("✅ Eliminados {$deletedMovimientos} movimientos de stock"); }
                }
            } catch (\Exception $e) { Log::info('ℹ️ Tabla movimientos_stock no existe o ya está limpia'); }
            
            // 3. Eliminar ubicaciones de productos en almacén (si existe)
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('producto_ubicaciones')) {
                    $deletedUbicaciones = DB::table('producto_ubicaciones')->where('producto_id', $id)->delete();
                    if ($deletedUbicaciones > 0) { Log::info("✅ Eliminadas {$deletedUbicaciones} ubicaciones de producto"); }
                }
            } catch (\Exception $e) { Log::info('ℹ️ Tabla producto_ubicaciones no existe o ya está limpia'); }
            
            // 4. Eliminar de otras tablas relacionadas que puedan existir
            $tablesWithProductoId = [
                'compra_detalles',
                'inventario_ajustes',
                'stock_minimo_alertas',
                'producto_proveedores',
                'promociones_productos',
                'alertas_productos'
            ];
            
            foreach ($tablesWithProductoId as $table) {
                try {
                    $deleted = DB::table($table)->where('producto_id', $id)->delete();
                    if ($deleted > 0) {
                        Log::info("✅ Eliminados {$deleted} registros de tabla {$table}");
                    }
                } catch (\Exception $e) {
                    Log::info("ℹ️ Tabla {$table} no existe o ya está limpia");
                }
            }
            
            // 6. Eliminar imagen si existe
            if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
                Log::info("✅ Imagen eliminada: {$producto->imagen}");
            }
            
            // 7. ELIMINAR EL PRODUCTO DIRECTAMENTE CON RAW SQL
            DB::statement('DELETE FROM productos WHERE id = ?', [$id]);
            Log::info("✅ Producto eliminado con SQL directo");
            
            // 8. REACTIVAR FOREIGN KEY CHECKS (si el driver lo soporta)
            try { DB::statement('SET FOREIGN_KEY_CHECKS=1;'); } catch (\Exception $fkOn) { Log::info('FK checks no soportados por el driver actual'); }
            
            DB::commit();
            Log::info("🎉 Eliminación completada exitosamente");

            return response()->json([
                'success' => true,
                'message' => "Producto '{$producto->nombre}' eliminado correctamente",
                'needsRefresh' => true
            ]);
            
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            
            // Asegurar que foreign keys estén activadas aunque falle
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Exception $fkError) {
                Log::error("Error reactivando foreign keys: " . $fkError->getMessage());
            }
            
            Log::error("❌ Error al eliminar producto ID {$id}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Mensaje de error más específico basado en el tipo de error
            $errorMessage = 'Error al eliminar el producto';
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                $errorMessage = 'No se puede eliminar el producto porque está siendo usado en ventas o compras';
            } elseif (str_contains($e->getMessage(), 'venta_detalles')) {
                $errorMessage = 'No se puede eliminar el producto porque tiene ventas asociadas';
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function determinarEstado($stockActual, $stockMinimo, $fechaVencimiento)
    {
        try {
            $hoy = now();
            
            // Asegurar que la fecha de vencimiento sea un objeto Carbon
            if (is_string($fechaVencimiento)) {
                $fechaVencimiento = Carbon::createFromFormat('Y-m-d', $fechaVencimiento);
            } elseif (!$fechaVencimiento instanceof Carbon) {
                $fechaVencimiento = Carbon::parse($fechaVencimiento);
            }
            
            $diasParaVencer = $hoy->diffInDays($fechaVencimiento, false);

            // 1. Verificar si está vencido
            if ($fechaVencimiento < $hoy) {
                return 'Vencido';
            }
            
            // 2. Verificar si está agotado (stock 0)
            if ($stockActual <= 0) {
                return 'Agotado';
            }
            
            // 3. Verificar si está próximo a vencer (30 días)
            if ($diasParaVencer <= 30) {
                return 'Por vencer';
            }
            
            // 4. Verificar si tiene stock bajo (mayor a 0 pero menor o igual al mínimo)
            if ($stockActual <= $stockMinimo) {
                return 'Bajo stock';
            }
            
            // 5. Estado normal
            return 'Normal';
        } catch (\Exception $e) {
            Log::error('Error en determinarEstado: ' . $e->getMessage());
            return 'Normal'; // Estado por defecto en caso de error
        }
    }

    public function categorias()
    {
        $categorias = Categoria::orderBy('id')->get();
        return view('inventario.categorias', compact('categorias'));
    }

    public function categoriasApi()
    {
        try {
            $categorias = Categoria::orderBy('nombre')
                ->get(['id', 'nombre', 'descripcion', 'estado']);
            return response()->json([
                'success' => true,
                'data' => $categorias
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener categorías: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las categorías'
            ], 500);
        }
    }

    public function presentacion()
    {
        $presentaciones = \App\Models\Presentacion::orderBy('id')->get();
        return view('inventario.presentacion', compact('presentaciones'));
    }

    /**
     * Cambiar estado de categoría (activar/desactivar)
     */
    public function cambiarEstadoCategoria($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            // Alternar estado string ('activo'/'inactivo')
            $categoria->estado = ($categoria->estado === 'activo') ? 'inactivo' : 'activo';
            $categoria->save();

            return response()->json(['success' => true, 'activo' => $categoria->estado === 'activo']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'No se pudo cambiar el estado'], 500);
        }
    }

    /**
     * Cambiar estado de presentación (activar/desactivar)
     */
    public function cambiarEstadoPresentacion($id)
    {
        try {
            $presentacion = \App\Models\Presentacion::findOrFail($id);
            // Alternar estado string ('activo'/'inactivo')
            $presentacion->estado = ($presentacion->estado === 'activo') ? 'inactivo' : 'activo';
            $presentacion->save();

            return response()->json(['success' => true, 'activo' => $presentacion->estado === 'activo']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'No se pudo cambiar el estado'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $producto = Producto::find($id);
            if (!$producto) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Producto no encontrado.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'categoria' => 'required|string|max:255',
                'marca' => 'required|string|max:255',
                'presentacion' => 'required|string|max:255',
                'concentracion' => 'nullable|string|max:100',
                'lote' => 'required|string|max:100',
                'codigo_barras' => 'required|string|max:255|unique:productos,codigo_barras,' . $id,
                'stock_actual' => 'required|integer|min:0',
                'stock_minimo' => 'required|integer|min:0',
                'precio_compra' => 'required|numeric|min:0',
                'precio_venta' => 'required|numeric|min:0|gt:precio_compra',
                'fecha_fabricacion' => 'required|date',
                'fecha_vencimiento' => 'required|date|after_or_equal:fecha_fabricacion',
                'ubicacion' => 'nullable|string|max:255',
                'proveedor_id' => 'required|integer|exists:proveedores,id',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Error de validación.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Preparar datos para actualizar (excluir campos que no deben ser actualizados)
            $datos = $request->except(['_method', '_token', 'imagen', 'id', 'producto_id']);

            // Manejo de la imagen
            if ($request->hasFile('imagen')) {
                $imageOptimizer = new ImageOptimizationService();
                
                // Eliminar la imagen anterior si existe
                if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                    $imageOptimizer->deleteProductImage($producto->imagen);
                }
                
                // Optimizar y guardar la nueva imagen
                $imagePath = $imageOptimizer->optimizeProductImage($request->file('imagen'));
                $datos['imagen'] = $imagePath;
            }

            // Actualizar el producto
            $producto->update($datos);
            
            // Recalcular el estado del producto después de actualizar
            $producto->fresh()->recalcularEstado();
            
            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Producto actualizado correctamente.',
                'data' => $producto
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar producto: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API para obtener todas las presentaciones
     */
    public function presentacionesApi()
    {
        try {
            $presentaciones = \App\Models\Presentacion::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']);
            return response()->json([
                'success' => true,
                'data' => $presentaciones
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener presentaciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las presentaciones'
            ], 500);
        }
    }

    /**
     * API para obtener un producto específico por ID
     */
    public function getProductoById($id)
    {
        try {
            $producto = Producto::with('proveedor')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'categoria' => $producto->categoria,
                    'marca' => $producto->marca,
                    'proveedor_id' => $producto->proveedor_id,
                    'proveedor' => $producto->proveedor ? $producto->proveedor->razon_social : null,
                    'presentacion' => $producto->presentacion,
                    'concentracion' => $producto->concentracion,
                    'lote' => $producto->lote,
                    'codigo_barras' => $producto->codigo_barras,
                    'stock_actual' => $producto->stock_actual,
                    'stock_minimo' => $producto->stock_minimo,
                    'precio_compra' => $producto->precio_compra,
                    'precio_venta' => $producto->precio_venta,
                    'fecha_fabricacion' => $producto->fecha_fabricacion_solo_fecha,
                    'fecha_vencimiento' => $producto->fecha_vencimiento_solo_fecha,
                    'ubicacion' => $producto->ubicacion,
                    'ubicacion_almacen' => $producto->ubicacion_almacen,
                    'imagen' => $producto->imagen,
                    'imagen_url' => $producto->imagen_url,
                    'estado' => $producto->estado
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener producto por ID: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el producto: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Reordena los IDs de los productos para mantener secuencia consecutiva
     * VERSIÓN SEGURA - Solo se ejecuta manualmente
     */
    public function reordenarIds()
    {
        try {
            DB::beginTransaction();
            
            Log::info("🔄 Iniciando reordenamiento seguro de IDs de productos");
            
            // Obtener todos los productos ordenados por ID actual
            $productos = Producto::orderBy('id')->get();
            
            if ($productos->count() === 0) {
                Log::info("ℹ️ No hay productos para reordenar");
                return response()->json([
                    'success' => true,
                    'message' => 'No hay productos para reordenar'
                ]);
            }
            
            // Deshabilitar foreign key checks temporalmente
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Mapear IDs antiguos a nuevos
            $idMapping = [];
            $counter = 1;
            
            foreach ($productos as $producto) {
                $idMapping[$producto->id] = $counter;
                $counter++;
            }
            
            // Actualizar referencias en tablas relacionadas PRIMERO
            foreach ($idMapping as $oldId => $newId) {
                if ($oldId != $newId) {
                    // Usar IDs temporales negativos para evitar conflictos
                    $tempId = -$newId;
                    
                    // Actualizar referencias en otras tablas
                    DB::statement('UPDATE venta_detalles SET producto_id = ? WHERE producto_id = ?', [$tempId, $oldId]);
                    DB::statement('UPDATE movimientos_stock SET producto_id = ? WHERE producto_id = ?', [$tempId, $oldId]);
                    DB::statement('UPDATE producto_ubicaciones SET producto_id = ? WHERE producto_id = ?', [$tempId, $oldId]);
                    
                    // Actualizar el producto
                    DB::statement('UPDATE productos SET id = ? WHERE id = ?', [$tempId, $oldId]);
                    
                    Log::info("✅ Paso 1: Producto ID {$oldId} → {$tempId} (temporal)");
                }
            }
            
            // Ahora convertir los IDs temporales a los finales
            foreach ($idMapping as $oldId => $newId) {
                if ($oldId != $newId) {
                    $tempId = -$newId;
                    
                    // Actualizar a IDs finales
                    DB::statement('UPDATE venta_detalles SET producto_id = ? WHERE producto_id = ?', [$newId, $tempId]);
                    DB::statement('UPDATE movimientos_stock SET producto_id = ? WHERE producto_id = ?', [$newId, $tempId]);
                    DB::statement('UPDATE producto_ubicaciones SET producto_id = ? WHERE producto_id = ?', [$newId, $tempId]);
                    DB::statement('UPDATE productos SET id = ? WHERE id = ?', [$newId, $tempId]);
                    
                    Log::info("✅ Paso 2: Producto ID {$tempId} → {$newId} (final)");
                }
            }
            
            // Reiniciar el AUTO_INCREMENT
            $nextId = $counter;
            DB::statement("ALTER TABLE productos AUTO_INCREMENT = {$nextId}");
            
            // Reactivar foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            DB::commit();
            
            Log::info("🎉 Reordenamiento completado. Próximo ID será: {$nextId}");
            
            return response()->json([
                'success' => true,
                'message' => "IDs reordenados correctamente. Próximo ID será: {$nextId}",
                'productos_procesados' => $productos->count()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("❌ Error en reordenamiento de IDs: " . $e->getMessage());
            
            // Asegurar que foreign keys estén activadas
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Exception $fkError) {
                Log::error("Error reactivando foreign keys: " . $fkError->getMessage());
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Error al reordenar IDs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todas las categorías para filtros
     */
    public function obtenerCategorias()
    {
        try {
            $categorias = Categoria::select(['id', 'nombre'])
                ->where('estado', 'activo')
                ->orderBy('nombre')
                ->get();

            return response()->json([
                'success' => true,
                'categorias' => $categorias
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categorías'
            ], 500);
        }
    }
}