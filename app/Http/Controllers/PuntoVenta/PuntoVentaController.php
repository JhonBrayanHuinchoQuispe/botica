<?php

namespace App\Http\Controllers\PuntoVenta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PuntoVenta\{Cliente, Venta, VentaDetalle};
use App\Models\Producto;
use App\Models\ConfiguracionSistema;
use App\Services\ReniecService;
use App\Services\FacturacionElectronicaService;
use App\Services\FacturacionElectronicaServiceBeta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PuntoVentaController extends Controller
{
    public function index()
    {
        return view('punto-venta.index');
    }

    /**
     * Obtener productos más vendidos
     */
    public function productosMasVendidos()
    {
        try {
            // Obtener los IDs de los productos más vendidos
            $productosVendidos = DB::table('venta_detalles')
                ->join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
                ->where('ventas.estado', 'completada')
                ->select(
                    'venta_detalles.producto_id',
                    DB::raw('SUM(venta_detalles.cantidad) as total_vendido')
                )
                ->groupBy('venta_detalles.producto_id')
                ->orderBy('total_vendido', 'desc')
                ->limit(10)
                ->get();

            if ($productosVendidos->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'productos' => [],
                    'message' => 'No hay productos vendidos aún'
                ]);
            }

            // Obtener los productos usando el modelo para tener acceso a los accessors
            $productosIds = $productosVendidos->pluck('producto_id');
            $productos = Producto::whereIn('id', $productosIds)
                ->with(['ubicaciones.ubicacion.estante'])
                ->where('stock_actual', '>', 0)
                ->get()
                ->keyBy('id');

            // Combinar con las cantidades vendidas
            $productosConVentas = $productosVendidos->map(function($venta) use ($productos) {
                $producto = $productos->get($venta->producto_id);
                if ($producto) {
                    $producto->total_vendido = $venta->total_vendido;
                    return $producto;
                }
                return null;
            })->filter();

            // Si no hay productos con stock, devolver lista vacía
            if ($productosConVentas->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'productos' => [],
                    'message' => 'No hay productos vendidos con stock disponible'
                ]);
            }

            // Formateo directo y rápido con información de vencimiento y ubicaciones
            $productosFormateados = $productosConVentas->map(function($producto) {
                // Calcular días para vencer
                $diasParaVencer = null;
                $estadoVencimiento = 'sin_fecha';
                
                if ($producto->fecha_vencimiento) {
                    $fechaVencimiento = \Carbon\Carbon::parse($producto->fecha_vencimiento);
                    $diasParaVencer = now()->diffInDays($fechaVencimiento, false);
                    $estadoVencimiento = $this->getEstadoVencimiento($diasParaVencer);
                }
                
                // Procesar ubicaciones detalladas
                $ubicacionesDetalle = [];
                $totalUbicaciones = 0;
                $stockEnUbicaciones = 0;
                
                if ($producto->ubicaciones && $producto->ubicaciones->count() > 0) {
                    $ubicacionesConStock = $producto->ubicaciones->where('cantidad', '>', 0);
                    $totalUbicaciones = $ubicacionesConStock->count();
                    $stockEnUbicaciones = $ubicacionesConStock->sum('cantidad');
                    
                    $ubicacionesDetalle = $ubicacionesConStock->map(function($ubicacion) {
                        $estante = $ubicacion->ubicacion?->estante;
                        return [
                            'ubicacion_completa' => ($estante?->nombre ?? 'Sin asignar') . ' - ' . ($ubicacion->ubicacion?->codigo ?? 'N/A'),
                            'codigo' => $ubicacion->ubicacion?->codigo ?? 'N/A',
                            'cantidad' => $ubicacion->cantidad,
                            'lote' => $ubicacion->lote,
                            'fecha_vencimiento' => $ubicacion->fecha_vencimiento?->format('Y-m-d')
                        ];
                    })->values()->toArray();
                }
                
                $stockSinUbicar = max(0, $producto->stock_actual - $stockEnUbicaciones);
                $tieneStockSinUbicar = $stockSinUbicar > 0;
                
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'concentracion' => $producto->concentracion,
                    'presentacion' => $producto->presentacion ?? 'Presentación estándar',
                    'precio_venta' => (float) $producto->precio_venta,
                    'stock_actual' => $producto->stock_actual,
                    'imagen' => $producto->imagen_url,
                    'ubicacion_almacen' => $producto->ubicacion_almacen,
                    'fecha_vencimiento' => $producto->fecha_vencimiento ? \Carbon\Carbon::parse($producto->fecha_vencimiento)->format('Y-m-d') : null,
                    'dias_para_vencer' => $diasParaVencer,
                    'estado_vencimiento' => $estadoVencimiento,
                    'total_vendido' => $producto->total_vendido ?? 0,
                    'estado' => 'disponible',
                    // Información de ubicaciones detalladas
                    'ubicaciones_detalle' => $ubicacionesDetalle,
                    'total_ubicaciones' => $totalUbicaciones,
                    'stock_en_ubicaciones' => $stockEnUbicaciones,
                    'stock_sin_ubicar' => $stockSinUbicar,
                    'tiene_stock_sin_ubicar' => $tieneStockSinUbicar
                ];
            });

            return response()->json([
                'success' => true,
                'productos' => $productosFormateados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos más vendidos',
                'productos' => []
            ], 500);
        }
    }

    /**
     * Buscar alternativas farmacológicas para un producto específico
     */
    public function buscarAlternativas(Request $request)
    {
        try {
            // 🔐 Verificar autenticación
            if (!auth()->check()) {
                Log::warning("❌ Usuario no autenticado intentando acceder a buscarAlternativas");
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            
            $termino = $request->get('q');
            
            // 🐛 DEBUG: Log del término recibido
            Log::info("🔍 Buscando alternativas para: " . $termino . " (Usuario: " . auth()->user()->name . ")");
            
            if (empty($termino)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Término de búsqueda requerido'
                ]);
            }

            // 🔍 BUSCAR EL PRODUCTO ORIGINAL DE MANERA MÁS PRECISA
            // Primero buscar coincidencia exacta
            $productoOriginal = Producto::whereRaw('LOWER(nombre) = ?', [strtolower($termino)])->first();
            
            // Si no encuentra exacto, buscar el que más se parezca
            if (!$productoOriginal) {
                $productoOriginal = Producto::where('nombre', 'like', "%{$termino}%")
                    ->orderByRaw('LENGTH(nombre) ASC') // Priorizar nombres más cortos (más específicos)
                    ->first();
            }
            
            // 🐛 DEBUG: Log del producto encontrado
            if ($productoOriginal) {
                Log::info("✅ Producto original encontrado: " . $productoOriginal->nombre . " (ID: " . $productoOriginal->id . ")");
            } else {
                Log::info("❌ No se encontró producto original para: " . $termino);
            }
            
            if (!$productoOriginal) {
                return response()->json([
                    'success' => true,
                    'alternativas' => [],
                    'mensaje' => 'Producto no encontrado para análisis farmacológico'
                ]);
            }

            $alternativas = collect();

            // 🐛 DEBUG: Log del producto original
            Log::info("🔍 Producto original: " . $productoOriginal->nombre . " (Categoría: " . $productoOriginal->categoria . ")");

            // 🧬 PASO 1: PRINCIPIO ACTIVO (Máxima prioridad farmacológica)
            $principioActivo = $this->extraerPrincipioActivo($productoOriginal->nombre);
            Log::info("🧬 Principio activo extraído: " . ($principioActivo ?: 'No encontrado'));
            
            if ($principioActivo) {
                $productosMismoPrincipio = Producto::where('stock_actual', '>', 0)
                    ->where('id', '!=', $productoOriginal->id)
                    ->where(function($query) use ($principioActivo) {
                        $query->where('nombre', 'like', "%{$principioActivo}%")
                              ->orWhere('concentracion', 'like', "%{$principioActivo}%");
                    })
                    ->orderBy('stock_actual', 'desc')
                    ->limit(4)
                    ->get();
                
                Log::info("🧬 Productos con mismo principio activo: " . $productosMismoPrincipio->count());
                $alternativas = $alternativas->merge($productosMismoPrincipio);
            }

            // 🎯 PASO 2: INDICACIÓN TERAPÉUTICA (Por categoría farmacológica)
            $indicacionTerapeutica = $this->obtenerIndicacionTerapeutica($productoOriginal->categoria);
            if ($indicacionTerapeutica) {
                $productosIndicacionSimilar = Producto::where('stock_actual', '>', 0)
                    ->where('id', '!=', $productoOriginal->id)
                    ->whereNotIn('id', $alternativas->pluck('id'))
                    ->whereIn('categoria', $indicacionTerapeutica)
                    ->orderBy('stock_actual', 'desc')
                    ->limit(3)
                    ->get();
                
                $alternativas = $alternativas->merge($productosIndicacionSimilar);
            }

            // 💊 PASO 3: CONCENTRACIÓN EQUIVALENTE
            if ($productoOriginal->concentracion) {
                $concentracionEquivalente = $this->buscarConcentracionEquivalente($productoOriginal->concentracion);
                if ($concentracionEquivalente) {
                    $productosConcentracionEquivalente = Producto::where('stock_actual', '>', 0)
                        ->where('id', '!=', $productoOriginal->id)
                        ->whereNotIn('id', $alternativas->pluck('id'))
                        ->whereIn('concentracion', $concentracionEquivalente)
                        ->orderBy('stock_actual', 'desc')
                        ->limit(3)
                        ->get();
                    
                    $alternativas = $alternativas->merge($productosConcentracionEquivalente);
                }
            }

            // 🏥 PASO 4: GRUPO FARMACOLÓGICO (Misma categoría)
            $productosGrupoFarmacologico = Producto::where('stock_actual', '>', 0)
                ->where('id', '!=', $productoOriginal->id)
                ->whereNotIn('id', $alternativas->pluck('id'))
                ->where('categoria', $productoOriginal->categoria)
                ->orderBy('stock_actual', 'desc')
                ->limit(3)
                ->get();
            
            $alternativas = $alternativas->merge($productosGrupoFarmacologico);

            // 🔄 PASO 5: FALLBACK - Productos relacionados por marca o presentación (solo si hay pocos resultados)
            if ($alternativas->count() < 5) {
                $productosRelacionados = Producto::where('stock_actual', '>', 0)
                    ->where('id', '!=', $productoOriginal->id)
                    ->whereNotIn('id', $alternativas->pluck('id'))
                    ->where(function($query) use ($productoOriginal) {
                        $query->where('marca', $productoOriginal->marca)
                              ->orWhere('presentacion', $productoOriginal->presentacion);
                    })
                    ->orderBy('stock_actual', 'desc')
                    ->limit(3)
                    ->get();
                
                $alternativas = $alternativas->merge($productosRelacionados);
            }

            // 📊 ORDENAR POR RELEVANCIA FARMACOLÓGICA
            $alternativasOrdenadas = $this->ordenarPorRelevanciaFarmacologica($alternativas, $productoOriginal);

            // Eliminar duplicados y limitar resultados
            $alternativasUnicas = $alternativasOrdenadas->unique('id')->take(8);

            // 🐛 DEBUG: Log del resultado final
            Log::info("📊 Total de alternativas únicas encontradas: " . $alternativasUnicas->count());
            foreach ($alternativasUnicas as $alt) {
                Log::info("  ✅ " . $alt->nombre . " (Stock: " . $alt->stock_actual . ")");
            }

            return response()->json([
                'success' => true,
                'producto_original' => $productoOriginal->nombre,
                'criterio_busqueda' => $this->generarCriterioBusqueda($productoOriginal),
                'alternativas' => $alternativasUnicas->map(function($producto) use ($productoOriginal) {
                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'concentracion' => $producto->concentracion,
                        'presentacion' => $producto->presentacion ?? 'Presentación estándar',
                        'precio_venta' => (float) $producto->precio_venta,
                        'stock_actual' => $producto->stock_actual,
                        'imagen' => $producto->imagen_url,
                        'ubicacion_almacen' => $producto->ubicacion_almacen,
                        'categoria' => $producto->categoria ?? 'Sin categoría',
                        'marca' => $producto->marca,
                        'similitud' => $this->calcularSimilitudFarmacologica($producto, $productoOriginal),
                        'razon_similitud' => $this->explicarSimilitud($producto, $productoOriginal),
                        'estado' => 'disponible'
                    ];
                })->values()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar alternativas farmacológicas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🧬 Extraer principio activo del nombre del producto
     */
    private function extraerPrincipioActivo($nombre)
    {
        // Diccionario ampliado de principios activos comunes
        $principiosActivos = [
            // Analgésicos y antiinflamatorios
            'paracetamol' => ['paracetamol', 'acetaminofen', 'acetaminofén'],
            'ibuprofeno' => ['ibuprofeno'],
            'aspirina' => ['aspirina', 'ácido acetilsalicílico', 'acido acetilsalicilico'],
            'diclofenaco' => ['diclofenaco'],
            'naproxeno' => ['naproxeno'],
            'ketorolaco' => ['ketorolaco'],
            'meloxicam' => ['meloxicam'],
            
            // Antibióticos
            'amoxicilina' => ['amoxicilina'],
            'azitromicina' => ['azitromicina'],
            'ciprofloxacino' => ['ciprofloxacino'],
            'cefalexina' => ['cefalexina'],
            'clindamicina' => ['clindamicina'],
            'doxiciclina' => ['doxiciclina'],
            'eritromicina' => ['eritromicina'],
            
            // Antihistamínicos
            'loratadina' => ['loratadina'],
            'cetirizina' => ['cetirizina'],
            'difenhidramina' => ['difenhidramina'],
            'clorfenamina' => ['clorfenamina'],
            
            // Gastroprotectores
            'omeprazol' => ['omeprazol'],
            'ranitidina' => ['ranitidina'],
            'pantoprazol' => ['pantoprazol'],
            'esomeprazol' => ['esomeprazol'],
            
            // Cardiovasculares
            'enalapril' => ['enalapril'],
            'losartan' => ['losartan'],
            'amlodipino' => ['amlodipino'],
            'atenolol' => ['atenolol'],
            'simvastatina' => ['simvastatina'],
            'atorvastatina' => ['atorvastatina'],
            
            // Antidiabéticos
            'metformina' => ['metformina'],
            'glibenclamida' => ['glibenclamida'],
            'insulina' => ['insulina'],
            
            // Vitaminas y suplementos
            'vitamina' => ['vitamina', 'complejo', 'tiamina', 'riboflavina', 'niacina', 'piridoxina', 'cobalamina'],
            'acido folico' => ['acido folico', 'ácido fólico', 'folato'],
            'calcio' => ['calcio'],
            'hierro' => ['hierro', 'sulfato ferroso'],
            
            // Antiespasmódicos
            'butilhioscina' => ['butilhioscina', 'hioscina'],
            'trimebutina' => ['trimebutina'],
            
            // Antivirales
            'aciclovir' => ['aciclovir'],
            
            // Antifúngicos
            'fluconazol' => ['fluconazol'],
            'ketoconazol' => ['ketoconazol'],
            
            // Broncodilatadores
            'salbutamol' => ['salbutamol'],
            'teofilina' => ['teofilina'],
            
            // Corticoides
            'prednisolona' => ['prednisolona'],
            'dexametasona' => ['dexametasona'],
            'hidrocortisona' => ['hidrocortisona']
        ];

        $nombreLower = strtolower($nombre);
        
        foreach ($principiosActivos as $principio => $variantes) {
            foreach ($variantes as $variante) {
                if (strpos($nombreLower, $variante) !== false) {
                    return $principio;
                }
            }
        }

        // Si no encuentra un principio activo conocido, usar la primera palabra significativa
        $palabras = explode(' ', $nombreLower);
        $palabrasSignificativas = array_filter($palabras, function($palabra) {
            // Filtrar palabras comunes que no son principios activos
            $palabrasComunes = ['mg', 'ml', 'g', 'tabletas', 'capsulas', 'jarabe', 'suspension', 'gotas', 'crema', 'gel', 'pomada'];
            return !in_array($palabra, $palabrasComunes) && strlen($palabra) > 3;
        });
        
        return !empty($palabrasSignificativas) ? array_values($palabrasSignificativas)[0] : null;
    }

    /**
     * 🎯 Obtener indicaciones terapéuticas relacionadas
     */
    private function obtenerIndicacionTerapeutica($categoria)
    {
        $indicacionesTerapeuticas = [
            // Analgésicos y antiinflamatorios
            'Analgesico' => ['Analgesico', 'Analgésicos', 'Antiinflamatorios', 'Antipiréticos', 'AINES'],
            'Analgésicos' => ['Analgesico', 'Analgésicos', 'Antiinflamatorios', 'Antipiréticos', 'AINES'],
            'Antiinflamatorios' => ['Antiinflamatorios', 'Analgésicos', 'Analgesico', 'AINES'],
            'Antipiréticos' => ['Antipiréticos', 'Analgésicos', 'Analgesico', 'Antiinflamatorios'],
            'AINES' => ['AINES', 'Antiinflamatorios', 'Analgésicos', 'Analgesico'],
            
            // Antimicrobianos
            'Antibióticos' => ['Antibióticos', 'Antimicrobianos', 'Antiinfecciosos', 'Antibioticos'],
            'Antibioticos' => ['Antibióticos', 'Antimicrobianos', 'Antiinfecciosos', 'Antibioticos'],
            'Antimicrobianos' => ['Antimicrobianos', 'Antibióticos', 'Antiinfecciosos'],
            'Antiinfecciosos' => ['Antiinfecciosos', 'Antibióticos', 'Antimicrobianos'],
            
            // Antihistamínicos y antialérgicos
            'Antihistamínicos' => ['Antihistamínicos', 'Antialérgicos', 'Antihistaminicos', 'Antialergicos'],
            'Antihistaminicos' => ['Antihistamínicos', 'Antialérgicos', 'Antihistaminicos', 'Antialergicos'],
            'Antialérgicos' => ['Antialérgicos', 'Antihistamínicos', 'Antialergicos'],
            'Antialergicos' => ['Antialérgicos', 'Antihistamínicos', 'Antialergicos'],
            
            // Cardiovasculares
            'Anticoagulantes' => ['Anticoagulantes', 'Antitrombóticos', 'Antiplaquetarios'],
            'Antihipertensivos' => ['Antihipertensivos', 'Cardiovasculares', 'IECA', 'ARA II', 'Betabloqueadores'],
            'Cardiovasculares' => ['Cardiovasculares', 'Antihipertensivos', 'Cardiotónicos'],
            'Hipolipemiantes' => ['Hipolipemiantes', 'Estatinas', 'Cardiovasculares'],
            
            // Digestivos
            'Digestivos' => ['Digestivos', 'Gastroprotectores', 'Antiespasmódicos', 'Antidiarreicos'],
            'Gastroprotectores' => ['Gastroprotectores', 'Digestivos', 'Antiácidos', 'IBP'],
            'Antiespasmódicos' => ['Antiespasmódicos', 'Digestivos', 'Espasmolíticos'],
            'Antidiarreicos' => ['Antidiarreicos', 'Digestivos', 'Probióticos'],
            'Antiácidos' => ['Antiácidos', 'Gastroprotectores', 'Digestivos'],
            
            // Vitaminas y suplementos
            'Vitaminas' => ['Vitaminas', 'Suplementos', 'Nutricionales', 'Multivitamínicos', 'VITAMINAS'],
            'VITAMINAS' => ['Vitaminas', 'Suplementos', 'Nutricionales', 'Multivitamínicos', 'VITAMINAS'],
            'Suplementos' => ['Suplementos', 'Vitaminas', 'Nutricionales', 'Minerales'],
            'Nutricionales' => ['Nutricionales', 'Vitaminas', 'Suplementos'],
            'Multivitamínicos' => ['Multivitamínicos', 'Vitaminas', 'Suplementos'],
            
            // Respiratorios
            'Broncodilatadores' => ['Broncodilatadores', 'Respiratorios', 'Antiasmáticos'],
            'Antitusivos' => ['Antitusivos', 'Respiratorios', 'Expectorantes'],
            'Expectorantes' => ['Expectorantes', 'Antitusivos', 'Respiratorios'],
            'Respiratorios' => ['Respiratorios', 'Broncodilatadores', 'Antitusivos'],
            
            // Endocrinos
            'Antidiabéticos' => ['Antidiabéticos', 'Hipoglucemiantes', 'Endocrinos'],
            'Hipoglucemiantes' => ['Hipoglucemiantes', 'Antidiabéticos', 'Endocrinos'],
            'Hormonas' => ['Hormonas', 'Endocrinos', 'Tiroideos'],
            'Corticoides' => ['Corticoides', 'Antiinflamatorios', 'Hormonas'],
            
            // Dermatológicos
            'Dermatológicos' => ['Dermatológicos', 'Tópicos', 'Antifúngicos tópicos'],
            'Antifúngicos' => ['Antifúngicos', 'Antimicóticos', 'Dermatológicos'],
            'Antimicóticos' => ['Antimicóticos', 'Antifúngicos', 'Dermatológicos'],
            
            // Neurológicos y psiquiátricos
            'Anticonvulsivantes' => ['Anticonvulsivantes', 'Neurológicos', 'Antiepilépticos'],
            'Ansiolíticos' => ['Ansiolíticos', 'Psiquiátricos', 'Sedantes'],
            'Antidepresivos' => ['Antidepresivos', 'Psiquiátricos'],
            
            // Oftalmológicos y otológicos
            'Oftalmológicos' => ['Oftalmológicos', 'Oculares'],
            'Otológicos' => ['Otológicos', 'Óticos'],
            
            // Ginecológicos y urológicos
            'Ginecológicos' => ['Ginecológicos', 'Hormonales'],
            'Urológicos' => ['Urológicos', 'Genitourinarios']
        ];

        return $indicacionesTerapeuticas[$categoria] ?? [$categoria];
    }

    /**
     * 💊 Buscar concentraciones equivalentes farmacológicamente
     */
    private function buscarConcentracionEquivalente($concentracion)
    {
        if (!$concentracion) return [];

        // Extraer número y unidad con regex mejorada
        preg_match('/(\d+(?:\.\d+)?)\s*([a-zA-Z%]+)/i', $concentracion, $matches);
        
        if (count($matches) < 3) return [$concentracion];

        $numero = floatval($matches[1]);
        $unidad = strtolower($matches[2]);

        $equivalentes = [$concentracion];

        // Concentraciones equivalentes farmacológicamente relevantes
        switch ($unidad) {
            case 'mg':
            case 'miligramos':
                // Buscar concentraciones similares (±25%, ±50%)
                $variaciones = [0.5, 0.75, 1.25, 1.5, 2.0];
                foreach ($variaciones as $factor) {
                    $nueva = round($numero * $factor);
                    if ($nueva > 0 && $nueva != $numero) {
                        $equivalentes[] = $nueva . 'mg';
                    }
                }
                
                // Conversiones a otras unidades
                if ($numero >= 1000) {
                    $equivalentes[] = ($numero / 1000) . 'g';
                }
                $equivalentes[] = ($numero * 1000) . 'mcg';
                
                // Concentraciones estándar comunes
                $estandares = [5, 10, 25, 50, 100, 200, 250, 500, 1000];
                foreach ($estandares as $std) {
                    if (abs($numero - $std) <= $numero * 0.5) {
                        $equivalentes[] = $std . 'mg';
                    }
                }
                break;
                
            case 'ml':
            case 'mililitros':
                // Para líquidos, buscar presentaciones similares
                $variaciones = [0.5, 2.0, 1.5];
                foreach ($variaciones as $factor) {
                    $nueva = round($numero * $factor, 1);
                    if ($nueva > 0) {
                        $equivalentes[] = $nueva . 'ml';
                    }
                }
                break;
                
            case 'g':
            case 'gramos':
                // Conversiones a mg y variaciones
                $equivalentes[] = ($numero * 1000) . 'mg';
                $equivalentes[] = round($numero * 0.5, 2) . 'g';
                $equivalentes[] = round($numero * 2, 2) . 'g';
                break;
                
            case 'mcg':
            case 'μg':
            case 'microgramos':
                // Conversiones y variaciones
                if ($numero >= 1000) {
                    $equivalentes[] = ($numero / 1000) . 'mg';
                }
                $equivalentes[] = round($numero * 0.5) . 'mcg';
                $equivalentes[] = round($numero * 2) . 'mcg';
                break;
                
            case 'ui':
            case 'iu':
            case 'unidades':
                // Para unidades internacionales
                $equivalentes[] = round($numero * 0.5) . 'UI';
                $equivalentes[] = round($numero * 2) . 'UI';
                $equivalentes[] = $numero . 'IU';
                break;
                
            case '%':
                // Para concentraciones porcentuales (tópicos)
                $equivalentes[] = round($numero * 0.5, 1) . '%';
                $equivalentes[] = round($numero * 2, 1) . '%';
                $equivalentes[] = ($numero * 10) . 'mg/ml';
                break;
        }

        // Limpiar duplicados y valores inválidos
        $equivalentes = array_unique(array_filter($equivalentes, function($conc) {
            return !empty($conc) && preg_match('/\d/', $conc);
        }));

        return array_values($equivalentes);
    }

    /**
     * 📊 Ordenar por relevancia farmacológica (PRIORIDAD: FUNCIÓN TERAPÉUTICA)
     */
    private function ordenarPorRelevanciaFarmacologica($productos, $productoOriginal)
    {
        return $productos->sortByDesc(function($producto) use ($productoOriginal) {
            $score = 0;

            // 🎯 PRIORIDAD MÁXIMA: MISMA FUNCIÓN TERAPÉUTICA (+200 puntos)
            if ($producto->categoria === $productoOriginal->categoria) {
                $score += 200; // Duplicado porque es lo más importante
            } else {
                // Verificar categorías relacionadas terapéuticamente
                $indicacionesRelacionadas = $this->obtenerIndicacionTerapeutica($productoOriginal->categoria);
                if (in_array($producto->categoria, $indicacionesRelacionadas)) {
                    $score += 150; // Función terapéutica relacionada
                }
            }

            // 💊 SEGUNDO: Mismo principio activo (+100 puntos)
            $principioOriginal = $this->extraerPrincipioActivo($productoOriginal->nombre);
            $principioProducto = $this->extraerPrincipioActivo($producto->nombre);
            if ($principioOriginal && $principioProducto && $principioOriginal === $principioProducto) {
                $score += 100;
            }

            // 📦 TERCERO: Misma presentación (+50 puntos) - importante para la forma de uso
            if ($producto->presentacion === $productoOriginal->presentacion) {
                $score += 50;
            } else {
                // Presentaciones relacionadas (tabletas ≈ cápsulas, crema ≈ pomada)
                $presentacionesRelacionadas = [
                    'tabletas' => ['comprimidos', 'cápsulas', 'grageas'],
                    'comprimidos' => ['tabletas', 'cápsulas', 'grageas'],
                    'cápsulas' => ['tabletas', 'comprimidos', 'grageas'],
                    'jarabe' => ['suspensión', 'solución oral', 'gotas'],
                    'suspensión' => ['jarabe', 'solución oral', 'gotas'],
                    'crema' => ['pomada', 'gel', 'ungüento'],
                    'pomada' => ['crema', 'gel', 'ungüento']
                ];
                
                $presentOrig = strtolower($productoOriginal->presentacion ?? '');
                $presentProd = strtolower($producto->presentacion ?? '');
                
                if (isset($presentacionesRelacionadas[$presentOrig]) && 
                    in_array($presentProd, $presentacionesRelacionadas[$presentOrig])) {
                    $score += 30;
                }
            }

            // 📊 CUARTO: Stock disponible (+40 puntos máximo)
            if ($producto->stock_actual > 0) {
                $score += 25; // Bonificación base por tener stock
                $score += min(($producto->stock_actual / 10) * 3, 15); // Bonificación por cantidad
            } else {
                $score -= 50; // Penalización por no tener stock
            }

            // 🏷️ QUINTO: Misma marca (+20 puntos) - menos importante que la función
            if ($producto->marca === $productoOriginal->marca) {
                $score += 20;
            }

            // 💰 SEXTO: Concentración similar (+10 puntos) - menos importante
            if ($producto->concentracion === $productoOriginal->concentracion) {
                $score += 10;
            }

            // 📅 SÉPTIMO: Vigencia del producto
            if ($producto->fecha_vencimiento) {
                $diasParaVencer = now()->diffInDays($producto->fecha_vencimiento, false);
                if ($diasParaVencer > 90) {
                    $score += 5; // Bonificación por buena vigencia
                } elseif ($diasParaVencer <= 0) {
                    $score -= 100; // Penalización fuerte por vencido
                } elseif ($diasParaVencer <= 30) {
                    $score -= 25; // Penalización por próximo a vencer
                }
            }

            return $score;
        });
    }

    /**
     * 🔍 Calcular similitud farmacológica (PRIORIDAD: FUNCIÓN TERAPÉUTICA)
     */
    private function calcularSimilitudFarmacologica($producto, $productoOriginal)
    {
        $similitud = 0;

        // 🎯 FUNCIÓN TERAPÉUTICA (50% del peso total)
        if ($producto->categoria === $productoOriginal->categoria) {
            $similitud += 50; // Misma función terapéutica
        } else {
            $indicacionesRelacionadas = $this->obtenerIndicacionTerapeutica($productoOriginal->categoria);
            if (in_array($producto->categoria, $indicacionesRelacionadas)) {
                $similitud += 35; // Función terapéutica relacionada
            }
        }

        // 💊 PRINCIPIO ACTIVO (25% del peso total)
        $principioOriginal = $this->extraerPrincipioActivo($productoOriginal->nombre);
        $principioProducto = $this->extraerPrincipioActivo($producto->nombre);
        if ($principioOriginal && $principioProducto && $principioOriginal === $principioProducto) {
            $similitud += 25;
        }

        // 📦 PRESENTACIÓN (15% del peso total)
        if ($producto->presentacion === $productoOriginal->presentacion) {
            $similitud += 15;
        } else {
            // Presentaciones relacionadas
            $presentacionesRelacionadas = [
                'tabletas' => ['comprimidos', 'cápsulas', 'grageas'],
                'comprimidos' => ['tabletas', 'cápsulas', 'grageas'],
                'cápsulas' => ['tabletas', 'comprimidos', 'grageas'],
                'jarabe' => ['suspensión', 'solución oral', 'gotas'],
                'crema' => ['pomada', 'gel', 'ungüento']
            ];
            
            $presentOrig = strtolower($productoOriginal->presentacion ?? '');
            $presentProd = strtolower($producto->presentacion ?? '');
            
            if (isset($presentacionesRelacionadas[$presentOrig]) && 
                in_array($presentProd, $presentacionesRelacionadas[$presentOrig])) {
                $similitud += 10;
            }
        }

        // 🏷️ MARCA (5% del peso total)
        if ($producto->marca === $productoOriginal->marca) {
            $similitud += 5;
        }

        // 💰 CONCENTRACIÓN (5% del peso total) - ahora menos importante
        if ($producto->concentracion === $productoOriginal->concentracion) {
            $similitud += 5;
        }

        return min($similitud, 100) . '%';
    }

    /**
     * 💬 Explicar por qué es similar (PRIORIDAD: FUNCIÓN TERAPÉUTICA)
     */
    private function explicarSimilitud($producto, $productoOriginal)
    {
        $razones = [];

        // 🎯 PRIORIDAD 1: Función terapéutica (lo más importante)
        if ($producto->categoria === $productoOriginal->categoria) {
            $razones[] = "✅ Misma función terapéutica ({$producto->categoria})";
        } else {
            $indicacionesRelacionadas = $this->obtenerIndicacionTerapeutica($productoOriginal->categoria);
            if (in_array($producto->categoria, $indicacionesRelacionadas)) {
                $razones[] = "🔗 Función terapéutica relacionada ({$producto->categoria})";
            }
        }

        // 💊 PRIORIDAD 2: Principio activo
        $principioOriginal = $this->extraerPrincipioActivo($productoOriginal->nombre);
        $principioProducto = $this->extraerPrincipioActivo($producto->nombre);
        
        if ($principioOriginal && $principioProducto && $principioOriginal === $principioProducto) {
            $razones[] = "💊 Mismo principio activo ({$principioOriginal})";
        }

        // 📦 PRIORIDAD 3: Forma de administración
        if ($producto->presentacion === $productoOriginal->presentacion) {
            $razones[] = "📦 Misma presentación ({$producto->presentacion})";
        }

        // 🏷️ Marca (menos importante)
        if ($producto->marca === $productoOriginal->marca) {
            $razones[] = "🏷️ Misma marca ({$producto->marca})";
        }

        // 💰 Concentración (menos importante ahora)
        if ($producto->concentracion === $productoOriginal->concentracion) {
            $razones[] = "⚖️ Misma concentración ({$producto->concentracion})";
        }

        // 📊 Stock
        if ($producto->stock_actual > 0) {
            $razones[] = "✅ Disponible en stock ({$producto->stock_actual} unidades)";
        } else {
            $razones[] = "⚠️ Sin stock disponible";
        }

        return empty($razones) ? 'Producto farmacéuticamente relacionado' : implode(' • ', $razones);
    }

    /**
     * 📋 Generar criterio de búsqueda
     */
    private function generarCriterioBusqueda($producto)
    {
        $principio = $this->extraerPrincipioActivo($producto->nombre);
        return "Buscando alternativas para {$producto->nombre}: " . 
               "Principio activo: " . ($principio ?: 'No identificado') . 
               ", Categoría: {$producto->categoria}" . 
               ", Concentración: {$producto->concentracion}";
    }

    /**
     * Formatear producto para el POS
     */
    private function formatearProductoParaPOS($producto)
    {
        // Calcular estado del producto
        $estado = $this->calcularEstadoProducto($producto);
        
        // Calcular días para vencer
        $diasParaVencer = null;
        if ($producto->fecha_vencimiento) {
            $diasParaVencer = now()->diffInDays($producto->fecha_vencimiento, false);
        }
        
        return [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'codigo_barras' => $producto->codigo_barras,
            'marca' => $producto->marca ?? 'Sin marca',
            'concentracion' => $producto->concentracion ?? '',
            'categoria' => $producto->categoria ?? 'Sin categoría',
            'categoria_id' => $producto->categoria_id,
            'presentacion' => $producto->presentacion ?? 'Sin presentación',
            'precio_compra' => $producto->precio_compra,
            'precio_venta' => $producto->precio_venta,
            'stock_actual' => $producto->stock_actual,
            'stock_minimo' => $producto->stock_minimo,
            'ubicacion_almacen' => $producto->ubicacion_almacen ?? 'Sin ubicar',
            'fecha_vencimiento' => $producto->fecha_vencimiento ? $producto->fecha_vencimiento->format('d/m/Y') : null,
            'fecha_vencimiento_raw' => $producto->fecha_vencimiento,
            'dias_para_vencer' => $diasParaVencer,
            'estado' => $estado,
            'imagen' => $producto->imagen_url,
            'proveedor_id' => $producto->proveedor_id,
            'es_favorito' => false, // Puede implementarse más adelante
            'descuento' => 0, // Puede implementarse más adelante
            'disponible_online' => $producto->stock_actual > 0
        ];
    }

    public function buscarProductos(Request $request)
    {
        $termino = $request->get('q');
        
        if (empty($termino) || strlen($termino) < 2) {
            return response()->json(['success' => true, 'productos' => []]);
        }
        
        // 🔥 BÚSQUEDA INTELIGENTE MEJORADA
        // 1. Búsqueda exacta por nombre completo (prioridad máxima)
        $productosExactos = Producto::select(['id', 'nombre', 'concentracion', 'presentacion', 'precio_venta', 'stock_actual', 'imagen', 'ubicacion_almacen', 'categoria', 'marca', 'fecha_vencimiento'])
            ->with(['ubicaciones.ubicacion.estante'])
            ->whereRaw('LOWER(nombre) = ?', [strtolower($termino)])
            ->orderBy('stock_actual', 'desc')
            ->limit(5)
            ->get();

        // 2. Búsqueda por nombres que empiecen con el término (prioridad alta)
        $productosInicio = Producto::select(['id', 'nombre', 'concentracion', 'presentacion', 'precio_venta', 'stock_actual', 'imagen', 'ubicacion_almacen', 'categoria', 'marca', 'fecha_vencimiento'])
            ->with(['ubicaciones.ubicacion.estante'])
            ->where('nombre', 'like', "{$termino}%")
            ->whereNotIn('id', $productosExactos->pluck('id'))
            ->orderBy('nombre', 'asc')
            ->limit(10)
            ->get();

        // 3. Búsqueda por nombres que contengan el término (prioridad media)
        $productosContienen = Producto::select(['id', 'nombre', 'concentracion', 'presentacion', 'precio_venta', 'stock_actual', 'imagen', 'ubicacion_almacen', 'categoria', 'marca', 'fecha_vencimiento'])
            ->with(['ubicaciones.ubicacion.estante'])
            ->where('nombre', 'like', "%{$termino}%")
            ->whereNotIn('id', $productosExactos->pluck('id')->merge($productosInicio->pluck('id')))
            ->orderBy('stock_actual', 'desc')
            ->limit(8)
            ->get();

        // 4. Búsqueda por principio activo (prioridad media-baja)
        $principioActivo = $this->extraerPrincipioActivo($termino);
        $productosPrincipio = collect();
        if ($principioActivo) {
            $productosPrincipio = Producto::select(['id', 'nombre', 'concentracion', 'presentacion', 'precio_venta', 'stock_actual', 'imagen', 'ubicacion_almacen', 'categoria', 'marca', 'fecha_vencimiento'])
                ->with(['ubicaciones.ubicacion.estante'])
                ->where('nombre', 'like', "%{$principioActivo}%")
                ->whereNotIn('id', $productosExactos->pluck('id')->merge($productosInicio->pluck('id'))->merge($productosContienen->pluck('id')))
                ->orderBy('stock_actual', 'desc')
                ->limit(5)
                ->get();
        }

        // 5. Búsqueda por marca (prioridad baja)
        $productosMarca = Producto::select(['id', 'nombre', 'concentracion', 'presentacion', 'precio_venta', 'stock_actual', 'imagen', 'ubicacion_almacen', 'categoria', 'marca', 'fecha_vencimiento'])
            ->with(['ubicaciones.ubicacion.estante'])
            ->where('marca', 'like', "%{$termino}%")
            ->whereNotIn('id', $productosExactos->pluck('id')->merge($productosInicio->pluck('id'))->merge($productosContienen->pluck('id'))->merge($productosPrincipio->pluck('id')))
            ->orderBy('stock_actual', 'desc')
            ->limit(3)
            ->get();

        // Combinar resultados manteniendo el orden de prioridad
        $productos = $productosExactos
            ->merge($productosInicio)
            ->merge($productosContienen)
            ->merge($productosPrincipio)
            ->merge($productosMarca)
            ->take(20); // Limitar a 20 resultados máximo

        // Formateo optimizado con información de vencimiento y ubicaciones
        $productosFormateados = $productos->map(function($producto) {
            // Calcular días para vencer
            $diasParaVencer = null;
            $estadoVencimiento = 'sin_fecha';
            
            if ($producto->fecha_vencimiento) {
                $diasParaVencer = now()->diffInDays($producto->fecha_vencimiento, false);
                $estadoVencimiento = $this->getEstadoVencimiento($diasParaVencer);
            }
            
            // Procesar ubicaciones detalladas
            $ubicacionesDetalle = [];
            $totalUbicaciones = 0;
            $stockEnUbicaciones = 0;
            
            if ($producto->ubicaciones && $producto->ubicaciones->count() > 0) {
                $ubicacionesConStock = $producto->ubicaciones->where('cantidad', '>', 0);
                $totalUbicaciones = $ubicacionesConStock->count();
                $stockEnUbicaciones = $ubicacionesConStock->sum('cantidad');
                
                $ubicacionesDetalle = $ubicacionesConStock->map(function($ubicacion) {
                    $estante = $ubicacion->ubicacion?->estante;
                    return [
                        'ubicacion_completa' => ($estante?->nombre ?? 'Sin asignar') . ' - ' . ($ubicacion->ubicacion?->codigo ?? 'N/A'),
                        'codigo' => $ubicacion->ubicacion?->codigo ?? 'N/A',
                        'cantidad' => $ubicacion->cantidad,
                        'lote' => $ubicacion->lote,
                        'fecha_vencimiento' => $ubicacion->fecha_vencimiento?->format('Y-m-d')
                    ];
                })->values()->toArray();
            }
            
            $stockSinUbicar = max(0, $producto->stock_actual - $stockEnUbicaciones);
            $tieneStockSinUbicar = $stockSinUbicar > 0;
            
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'concentracion' => $producto->concentracion,
                'presentacion' => $producto->presentacion ?? 'Presentación estándar',
                'precio_venta' => (float) $producto->precio_venta,
                'stock_actual' => $producto->stock_actual,
                'imagen' => $producto->imagen_url,
                'ubicacion_almacen' => $producto->ubicacion_almacen,
                'categoria' => $producto->categoria ?? 'Sin categoría',
                'marca' => $producto->marca ?? 'Sin marca',
                'fecha_vencimiento' => $producto->fecha_vencimiento ? $producto->fecha_vencimiento->format('Y-m-d') : null,
                'dias_para_vencer' => $diasParaVencer,
                'estado_vencimiento' => $estadoVencimiento,
                'estado' => $producto->stock_actual > 0 ? 'disponible' : 'sin_stock',
                // Información de ubicaciones detalladas
                'ubicaciones_detalle' => $ubicacionesDetalle,
                'total_ubicaciones' => $totalUbicaciones,
                'stock_en_ubicaciones' => $stockEnUbicaciones,
                'stock_sin_ubicar' => $stockSinUbicar,
                'tiene_stock_sin_ubicar' => $tieneStockSinUbicar
            ];
        });

        return response()->json([        
            'success' => true,
            'productos' => $productosFormateados
        ]);
    }

    /**
     * Calcular el estado del producto basado en stock y vencimiento
     */
    private function calcularEstadoProducto($producto)
    {
        // Verificar si está vencido
        if ($producto->fecha_vencimiento && $producto->fecha_vencimiento->isPast()) {
            return [
                'codigo' => 'vencido',
                'texto' => 'Vencido',
                'color' => '#dc3545',
                'icono' => '⚫'
            ];
        }
        
        // Verificar si está próximo a vencer (30 días)
        if ($producto->fecha_vencimiento) {
            $diasParaVencer = now()->diffInDays($producto->fecha_vencimiento, false);
            if ($diasParaVencer <= 30) {
                return [
                    'codigo' => 'por_vencer',
                    'texto' => 'Por vencer',
                    'color' => '#f59e0b',
                    'icono' => '🟠'
                ];
            }
        }
        
        // Verificar stock bajo
        if ($producto->stock_actual <= $producto->stock_minimo) {
            return [
                'codigo' => 'stock_bajo',
                'texto' => 'Stock bajo',
                'color' => '#ef4444',
                'icono' => '🔴'
            ];
        }
        
        // Verificar stock crítico (menos del 50% del mínimo)
        if ($producto->stock_actual <= ($producto->stock_minimo * 0.5)) {
            return [
                'codigo' => 'stock_critico',
                'texto' => 'Stock crítico',
                'color' => '#b91c1c',
                'icono' => '🚨'
            ];
        }
        
        // Normal
        return [
            'codigo' => 'normal',
            'texto' => 'Normal',
            'color' => '#10b981',
            'icono' => '🟢'
        ];
    }
    
    /**
     * Obtener estado de vencimiento
     */
    private function getEstadoVencimiento($diasParaVencer)
    {
        if ($diasParaVencer === null) {
            return 'sin_fecha';
        }
        
        if ($diasParaVencer < 0) {
            return 'vencido';
        }
        
        if ($diasParaVencer <= 15) {
            return 'urgente';
        }
        
        if ($diasParaVencer <= 30) {
            return 'proximo';
        }
        
        if ($diasParaVencer <= 60) {
            return 'moderado';
        }
        
        return 'seguro';
    }

    public function consultarDni(Request $request)
    {
        $dni = $request->input('dni');

        if (strlen($dni) !== 8 || !is_numeric($dni)) {
            return response()->json([
                'success' => false,
                'message' => 'DNI debe tener 8 dígitos'
            ]);
        }

        // Primero buscar en la base de datos local
        $cliente = Cliente::buscarPorDni($dni)->first();
        
        if ($cliente) {
            return response()->json([
                'success' => true,
                'cliente' => [
                    'id' => $cliente->id,
                    'dni' => $cliente->dni,
                    'nombre_completo' => $cliente->nombre_completo,
                    'nombres' => $cliente->nombres,
                    'apellido_paterno' => $cliente->apellido_paterno,
                    'apellido_materno' => $cliente->apellido_materno
                ],
                'message' => 'Cliente encontrado en base de datos local'
            ]);
        }

        // Si no existe, consultar API de RENIEC
        try {
            $reniecService = new ReniecService();
            $datosPersona = $reniecService->consultarDni($dni);
            
            if ($datosPersona && $reniecService->validarDatos($datosPersona)) {
                // Crear cliente en la base de datos
                $nuevoCliente = Cliente::create([
                    'dni' => $dni,
                    'nombres' => $datosPersona['nombres'],
                    'apellido_paterno' => $datosPersona['apellido_paterno'],
                    'apellido_materno' => $datosPersona['apellido_materno'],
                    'activo' => true
                ]);

                Log::info("✅ Cliente creado exitosamente desde {$datosPersona['fuente']}: " . $nuevoCliente->nombre_completo);

                return response()->json([
                    'success' => true,
                    'cliente' => [
                        'id' => $nuevoCliente->id,
                        'dni' => $nuevoCliente->dni,
                        'nombre_completo' => $nuevoCliente->nombre_completo,
                        'nombres' => $nuevoCliente->nombres,
                        'apellido_paterno' => $nuevoCliente->apellido_paterno,
                        'apellido_materno' => $nuevoCliente->apellido_materno
                    ],
                    'message' => "Cliente encontrado y registrado desde RENIEC ({$datosPersona['fuente']})"
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('❌ Error consultando DNI: ' . $e->getMessage());
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo consultar el DNI. Verifique el número e intente nuevamente. Puede que el DNI no exista o los servicios de RENIEC estén temporalmente no disponibles.'
        ]);
    }

    public function procesarVenta(Request $request)
    {
        // Validación rápida
        $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|integer',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string|in:efectivo,tarjeta,yape',
            'tipo_comprobante' => 'required|string|in:ticket,boleta',
            'efectivo_recibido' => 'nullable|numeric|min:0',
            'descuento_tipo' => 'nullable|string|in:porcentaje,monto',
            'descuento_valor' => 'nullable|numeric|min:0',
        ]);

        // Validación específica para efectivo
        if ($request->metodo_pago === 'efectivo') {
            // Obtener configuración para cálculo correcto
            $configuracion = ConfiguracionSistema::obtenerConfiguracion();
            
            $subtotalTemp = collect($request->productos)->sum(function($producto) {
                return $producto['precio'] * $producto['cantidad'];
            });
            
            // Calcular descuento temporal si existe
            $descuentoTemp = 0;
            if ($request->descuento_tipo && $request->descuento_valor > 0) {
                if ($request->descuento_tipo === 'porcentaje') {
                    $descuentoTemp = $subtotalTemp * (min($request->descuento_valor, $configuracion->descuento_maximo_porcentaje) / 100);
                } else {
                    $descuentoTemp = min($request->descuento_valor, $subtotalTemp);
                }
            }
            
            $subtotalConDescuentoTemp = $subtotalTemp - $descuentoTemp;
            
            // Calcular IGV según configuración del sistema
            $igvTemp = 0;
            if ($configuracion->igv_habilitado) {
                $igvTemp = $subtotalConDescuentoTemp * ($configuracion->igv_porcentaje / 100);
            }
            
            $totalTemp = $subtotalConDescuentoTemp + $igvTemp;
            $efectivoRecibido = $request->efectivo_recibido ?? 0;
            
            if ($efectivoRecibido < $totalTemp) {
                return response()->json([
                    'success' => false,
                    'message' => 'El efectivo recibido es insuficiente para cubrir el total de la venta',
                    'total_requerido' => number_format($totalTemp, 2),
                    'efectivo_recibido' => number_format($efectivoRecibido, 2)
                ], 400);
            }
        }

        try {
            DB::beginTransaction();
            
            // Obtener configuración del sistema
            $configuracion = ConfiguracionSistema::obtenerConfiguracion();

            // Calcular totales rápido
            $subtotal = collect($request->productos)->sum(function($producto) {
                return $producto['precio'] * $producto['cantidad'];
            });
            
            // Calcular descuento
            $descuentoMonto = 0;
            $descuentoPorcentaje = 0;
            
            if ($request->descuento_tipo && $request->descuento_valor > 0) {
                if ($request->descuento_tipo === 'porcentaje') {
                    $descuentoPorcentaje = min($request->descuento_valor, $configuracion->descuento_maximo_porcentaje);
                    $descuentoMonto = round($subtotal * ($descuentoPorcentaje / 100), 2);
                } else {
                    $descuentoMonto = min($request->descuento_valor, $subtotal);
                    $descuentoPorcentaje = round(($descuentoMonto / $subtotal) * 100, 2);
                }
            }
            
            // Subtotal con descuento
            $subtotalConDescuento = $subtotal - $descuentoMonto;
            
            // Calcular IGV según configuración
            $igv = 0;
            if ($configuracion->igv_habilitado) {
                $igv = round($subtotalConDescuento * ($configuracion->igv_porcentaje / 100), 2);
            }
            
            $total = round($subtotalConDescuento + $igv, 2);

            // Verificar stock de productos con bloqueo para prevenir race conditions
            $productosIds = collect($request->productos)->pluck('id');
            $productosDB = Producto::whereIn('id', $productosIds)
                                 ->lockForUpdate() // Bloquear registros durante la transacción
                                 ->get()
                                 ->keyBy('id');
            
            // Validar stock disponible
            $erroresStock = [];
            foreach ($request->productos as $producto) {
                $productoModel = $productosDB[$producto['id']] ?? null;
                if (!$productoModel) {
                    $erroresStock[] = "Producto ID {$producto['id']} no encontrado";
                    continue;
                }
                
                if ($productoModel->stock_actual < $producto['cantidad']) {
                    $erroresStock[] = "Stock insuficiente para '{$productoModel->nombre}'. Disponible: {$productoModel->stock_actual}, Solicitado: {$producto['cantidad']}";
                }
            }
            
            if (!empty($erroresStock)) {
                throw new \Exception("Errores de stock: " . implode('; ', $erroresStock));
            }
            
            // Calcular vuelto solo para pagos en efectivo
            $vuelto = 0;
            $efectivoRecibido = 0;
            
            if ($request->metodo_pago === 'efectivo') {
                $efectivoRecibido = $request->efectivo_recibido ?? 0;
                if ($efectivoRecibido > 0) {
                    $vuelto = max(0, round($efectivoRecibido - $total, 2));
                }
            }

            // Crear venta rápida
            $venta = Venta::create([
                'numero_venta' => 'V-' . date('Ymd') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                'usuario_id' => Auth::id() ?? 1,
                'tipo_comprobante' => $request->tipo_comprobante,
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total,
                'descuento_porcentaje' => $descuentoPorcentaje,
                'descuento_monto' => $descuentoMonto,
                'descuento_tipo' => $request->descuento_tipo,
                'descuento_razon' => $request->descuento_tipo ? 'Descuento aplicado en venta' : null,
                'igv_incluido' => $configuracion->igv_habilitado,
                'metodo_pago' => $request->metodo_pago,
                'efectivo_recibido' => $efectivoRecibido,
                'vuelto' => $vuelto,
                'estado' => 'completada',
                'fecha_venta' => now()
            ]);

            // Insertar detalles de venta en lote
            $detallesVenta = [];
            $actualizacionesStock = [];
            
            foreach ($request->productos as $producto) {
                $detallesVenta[] = [
                    'venta_id' => $venta->id,
                    'producto_id' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio'],
                    'subtotal' => round($producto['precio'] * $producto['cantidad'], 2),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                $actualizacionesStock[] = [
                    'id' => $producto['id'],
                    'cantidad' => $producto['cantidad']
                ];
            }
            
            // Insertar todos los detalles de una vez
            VentaDetalle::insert($detallesVenta);
            
            // Actualizar stock de productos y disparar observers
            foreach ($actualizacionesStock as $actualizacion) {
                $producto = Producto::find($actualizacion['id']);
                $producto->update([
                    'stock_actual' => $producto->stock_actual - $actualizacion['cantidad']
                ]);
            }
            
            DB::commit();

            // Respuesta optimizada y rápida
            return response()->json([
                'success' => true,
                'message' => 'Venta procesada exitosamente',
                'venta' => [
                    'id' => $venta->id,
                    'numero_venta' => $venta->numero_venta,
                    'total' => number_format($venta->total, 2, '.', ''),
                    'vuelto' => number_format($venta->vuelto, 2, '.', ''),
                    'metodo_pago' => $venta->metodo_pago,
                    'tipo_comprobante' => $venta->tipo_comprobante
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            
            // Log del error para debugging
            Log::error('Error procesando venta: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'productos' => $request->productos ?? [],
                'usuario_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage(),
                'error' => [
                    'code' => $e->getCode(),
                    'type' => get_class($e)
                ]
            ], 500);
        }
    }

    public function vistaPrevia($ventaId)
    {
        $venta = Venta::with(['cliente', 'detalles.producto', 'usuario'])
                     ->findOrFail($ventaId);

        return view('punto-venta.vista-previa', compact('venta'));
    }
    
    /**
     * Obtener estadísticas del día
     */
    public function estadisticasHoy()
    {
        try {
            $hoy = now()->format('Y-m-d');
            
            $ventas = Venta::whereDate('fecha_venta', $hoy)
                          ->where('estado', 'completada')
                          ->count();
                          
            $total = Venta::whereDate('fecha_venta', $hoy)
                         ->where('estado', 'completada')
                         ->sum('total');
            
            return response()->json([
                'success' => true,
                'ventas' => $ventas,
                'total' => $total
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'ventas' => 0,
                'total' => 0
            ]);
        }
    }
    
    /**
     * Obtener siguiente correlativo para SUNAT
     */
    private function obtenerSiguienteCorrelativo()
    {
        $serie = config('sunat.series.boleta');
        
        // Buscar el último correlativo en la base de datos
        $ultimaVenta = Venta::where('serie_sunat', $serie)
            ->whereNotNull('correlativo_sunat')
            ->orderBy('correlativo_sunat', 'desc')
            ->first();
            
        if ($ultimaVenta) {
            $ultimoCorrelativo = (int) $ultimaVenta->correlativo_sunat;
            return str_pad($ultimoCorrelativo + 1, 8, '0', STR_PAD_LEFT);
        }
        
        return '00000001';
    }


}