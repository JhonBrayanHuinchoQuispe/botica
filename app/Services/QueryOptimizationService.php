<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\User;
use App\Models\PuntoVenta\Venta;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QueryOptimizationService
{
    /**
     * Obtener productos con todas las relaciones optimizadas
     */
    public function getProductosOptimizados($filters = [])
    {
        $query = Producto::with([
            'ubicaciones.ubicacion.estante',
            // Proveedor: algunas instalaciones no tienen columna 'nombre'; usar razon_social y nombre_comercial
            'proveedor:id,razon_social,nombre_comercial,ruc',
            'categoria_model:id,nombre',
            'presentacion_model:id,nombre'
        ]);

        // Aplicar filtros
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('categoria', 'LIKE', "%{$search}%")
                  ->orWhere('marca', 'LIKE', "%{$search}%")
                  ->orWhere('codigo_barras', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['estado']) && $filters['estado'] !== 'todos') {
            $query->where('estado', $filters['estado']);
        }

        if (isset($filters['categoria']) && !empty($filters['categoria'])) {
            $query->where('categoria', $filters['categoria']);
        }

        if (isset($filters['stock_bajo']) && $filters['stock_bajo']) {
            $query->whereColumn('stock_actual', '<=', 'stock_minimo');
        }

        return $query->orderBy('id');
    }

    /**
     * Obtener productos más vendidos con eager loading
     */
    public function getProductosMasVendidosOptimizado($limit = 10)
    {
        try {
            return Cache::remember("productos_mas_vendidos_{$limit}", 600, function () use ($limit) {
                return $this->calculateProductosMasVendidos($limit);
            });
        } catch (\Exception $e) {
            // Si falla el cache, ejecutar directamente
            return $this->calculateProductosMasVendidos($limit);
        }
    }

    public function calculateProductosMasVendidos($limit)
    {
        return DB::table('venta_detalles')
            ->select(
                'productos.id', 'productos.nombre', 'productos.precio_venta', 'productos.stock_actual',
                'productos.presentacion', 'productos.fecha_vencimiento', 'productos.ubicacion_almacen', 'productos.imagen',
                DB::raw('SUM(venta_detalles.cantidad) as total_vendido'),
                DB::raw('SUM(venta_detalles.subtotal) as ingresos_totales'),
                // Usar fecha_venta si existe, o created_at como respaldo
                DB::raw('COALESCE(ventas.fecha_venta, ventas.created_at) as fecha_venta')
            )
            ->join('productos', 'venta_detalles.producto_id', '=', 'productos.id')
            ->join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->whereRaw('COALESCE(ventas.fecha_venta, ventas.created_at) >= ?', [now()->subDays(30)])
            ->groupBy('productos.id', 'productos.nombre', 'productos.precio_venta', 'productos.stock_actual', 'productos.presentacion', 'productos.fecha_vencimiento', 'productos.ubicacion_almacen', 'productos.imagen', DB::raw('COALESCE(ventas.fecha_venta, ventas.created_at)'))
            ->orderBy('total_vendido', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener ventas con eager loading optimizado
     */
    public function getVentasOptimizadas($filters = [])
    {
        $query = Venta::with([
            'detalles.producto:id,nombre,marca,categoria,precio_venta',
            'usuario:id,name,email',
            'cliente:id,nombre,email,telefono'
        ]);

        // Aplicar filtros
        if (isset($filters['fecha_inicio']) && isset($filters['fecha_fin'])) {
            $query->whereBetween('created_at', [$filters['fecha_inicio'], $filters['fecha_fin']]);
        }

        if (isset($filters['estado']) && !empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (isset($filters['usuario_id']) && !empty($filters['usuario_id'])) {
            $query->where('usuario_id', $filters['usuario_id']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Obtener dashboard data con consultas optimizadas
     */
    public function getDashboardDataOptimizado()
    {
        try {
            return Cache::remember('dashboard_data', 300, function() {
                return $this->calculateDashboardData();
            });
        } catch (\Exception $e) {
            // Si falla el cache (Redis no disponible), ejecutar directamente
            return $this->calculateDashboardData();
        }
    }

    public function calculateDashboardData()
    {
        // Productos con stock crítico (una sola consulta)
        $productosStockCritico = Producto::select('id', 'nombre', 'stock_actual', 'stock_minimo', 'categoria')
            ->where(function($query) {
                $query->where('stock_actual', '<=', 5)
                      ->orWhereColumn('stock_actual', '<=', 'stock_minimo');
            })
            ->orderBy('stock_actual', 'asc')
            ->limit(5)
            ->get();

        // Productos próximos a vencer (una sola consulta)
        $productosProximosVencer = Producto::select('id', 'nombre', 'fecha_vencimiento', 'stock_actual')
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '>', now())
            ->where('fecha_vencimiento', '<=', now()->addDays(30))
            ->orderBy('fecha_vencimiento', 'asc')
            ->limit(5)
            ->get();

        // Ventas del día (consulta optimizada)
        $ventasHoy = Venta::where('created_at', '>=', now()->startOfDay())
            ->where('estado', 'completada')
            ->sum('total');

        // Ventas del mes (consulta optimizada)
        $ventasMes = Venta::where('created_at', '>=', now()->startOfMonth())
            ->where('estado', 'completada')
            ->sum('total');

        // Usuarios activos (consulta optimizada)
        $usuariosActivos = User::select('id', 'name', 'email', 'last_login_at')
            ->whereNotNull('last_login_at')
            ->orderBy('last_login_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'productos_stock_critico' => $productosStockCritico,
            'productos_proximos_vencer' => $productosProximosVencer,
            'ventas_hoy' => $ventasHoy,
            'ventas_mes' => $ventasMes,
            'usuarios_activos' => $usuariosActivos
        ];
    }

    /**
     * Obtener estadísticas de ventas por período con consultas optimizadas
     */
    public function getVentasPorPeriodoOptimizado($periodo = '7d')
    {
        $cacheKey = "ventas_periodo_{$periodo}";
        
        return Cache::remember($cacheKey, 600, function() use ($periodo) {
            $fechaInicio = match($periodo) {
                '7d' => now()->subDays(7),
                '30d' => now()->subDays(30),
                '90d' => now()->subDays(90),
                default => now()->subDays(7)
            };

            return DB::table('ventas')
                ->select(
                    DB::raw('DATE(created_at) as fecha'),
                    DB::raw('COUNT(*) as total_ventas'),
                    DB::raw('SUM(total) as total_ingresos'),
                    DB::raw('SUM(cantidad_productos) as total_productos')
                )
                ->where('created_at', '>=', $fechaInicio)
                ->where('estado', 'completada')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('fecha', 'asc')
                ->get();
        });
    }

    /**
     * Obtener productos críticos con una sola consulta optimizada
     */
    public function getProductosCriticosOptimizado()
    {
        return Cache::remember('productos_criticos', 300, function() {
            return Producto::select([
                'id', 'nombre', 'marca', 'categoria', 'stock_actual', 
                'stock_minimo', 'fecha_vencimiento', 'precio_venta'
            ])
            ->where(function($query) {
                // Stock bajo
                $query->where('stock_actual', '<=', 5)
                      ->orWhereColumn('stock_actual', '<=', 'stock_minimo')
                      // Productos vencidos
                      ->orWhere(function($q) {
                          $q->whereNotNull('fecha_vencimiento')
                            ->where('fecha_vencimiento', '<', now());
                      })
                      // Productos próximos a vencer (30 días)
                      ->orWhere(function($q) {
                          $q->whereNotNull('fecha_vencimiento')
                            ->where('fecha_vencimiento', '>', now())
                            ->where('fecha_vencimiento', '<=', now()->addDays(30));
                      })
                      // Productos agotados
                      ->orWhere('stock_actual', 0);
            })
            ->orderByRaw('
                CASE 
                    WHEN stock_actual = 0 THEN 1
                    WHEN fecha_vencimiento < NOW() THEN 2
                    WHEN fecha_vencimiento <= DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 3
                    WHEN stock_actual <= stock_minimo THEN 4
                    ELSE 5
                END
            ')
            ->get();
        });
    }

    /**
     * Limpiar cache de consultas optimizadas
     */
    public function clearOptimizationCache()
    {
        $cacheKeys = [
            'dashboard_data',
            'productos_criticos',
            'productos_mas_vendidos_10',
            'ventas_periodo_7d',
            'ventas_periodo_30d',
            'ventas_periodo_90d'
        ];

        try {
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            return true;
        } catch (\Exception $e) {
            // Si falla el cache, no hay problema
            return false;
        }
    }

    /**
     * Obtener consultas lentas para análisis
     */
    public function getSlowQueries()
    {
        // Habilitar log de consultas lentas
        DB::enableQueryLog();
        
        // Ejecutar algunas consultas de prueba
        $start = microtime(true);
        
        // Consulta sin optimizar (ejemplo)
        $productosNoOptimizados = Producto::all();
        foreach ($productosNoOptimizados as $producto) {
            // Esto causaría N+1 si accedemos a relaciones
            $ubicaciones = $producto->ubicaciones; // N+1 problem
        }
        
        $timeNoOptimizado = microtime(true) - $start;
        
        // Consulta optimizada
        $start = microtime(true);
        $productosOptimizados = Producto::with('ubicaciones')->get();
        $timeOptimizado = microtime(true) - $start;
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        
        return [
            'queries' => $queries,
            'time_no_optimizado' => $timeNoOptimizado,
            'time_optimizado' => $timeOptimizado,
            'mejora_rendimiento' => (($timeNoOptimizado - $timeOptimizado) / $timeNoOptimizado) * 100
        ];
    }
}