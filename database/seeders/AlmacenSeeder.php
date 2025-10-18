<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Estante;
use App\Models\Ubicacion;
use App\Models\Producto;
use App\Models\ProductoUbicacion;
use App\Models\MovimientoStock;
use Illuminate\Support\Facades\DB;

class AlmacenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            // Crear estantes de ejemplo
            $estantes = [
                [
                    'nombre' => 'Estante A',
                    'descripcion' => 'Estante principal para medicamentos de venta libre',
                    'numero_niveles' => 4,
                    'numero_posiciones' => 5,
                    'tipo' => 'venta',
                    'ubicacion_fisica' => 'Zona frontal derecha'
                ],
                [
                    'nombre' => 'Estante B',
                    'descripcion' => 'Estante para vitaminas y suplementos',
                    'numero_niveles' => 3,
                    'numero_posiciones' => 6,
                    'tipo' => 'venta',
                    'ubicacion_fisica' => 'Zona central'
                ],
                [
                    'nombre' => 'Estante C',
                    'descripcion' => 'Estante de almacén interno',
                    'numero_niveles' => 5,
                    'numero_posiciones' => 4,
                    'tipo' => 'almacen',
                    'ubicacion_fisica' => 'Bodega posterior'
                ],
                [
                    'nombre' => 'Estante D',
                    'descripcion' => 'Estante para productos controlados',
                    'numero_niveles' => 2,
                    'numero_posiciones' => 8,
                    'tipo' => 'almacen',
                    'ubicacion_fisica' => 'Área restringida'
                ]
            ];

            foreach ($estantes as $estanteData) {
                $estante = Estante::create([
                    'nombre' => $estanteData['nombre'],
                    'descripcion' => $estanteData['descripcion'],
                    'numero_niveles' => $estanteData['numero_niveles'],
                    'numero_posiciones' => $estanteData['numero_posiciones'],
                    'capacidad_total' => $estanteData['numero_niveles'] * $estanteData['numero_posiciones'],
                    'tipo' => $estanteData['tipo'],
                    'ubicacion_fisica' => $estanteData['ubicacion_fisica'],
                    'activo' => true
                ]);

                // Las ubicaciones se crean automáticamente mediante el evento en el modelo
                echo "Estante {$estante->nombre} creado con {$estante->ubicaciones()->count()} ubicaciones.\n";
            }

            // Obtener algunos productos existentes para ubicar
            $productos = Producto::take(15)->get();
            
            if ($productos->count() > 0) {
                // Ubicar algunos productos aleatoriamente
                $estantesCreados = Estante::with('ubicaciones')->get();
                
                foreach ($productos as $index => $producto) {
                    // Seleccionar estante aleatorio
                    $estante = $estantesCreados->random();
                    $ubicacionesLibres = $estante->ubicaciones()->libres()->get();
                    
                    if ($ubicacionesLibres->count() > 0) {
                        $ubicacion = $ubicacionesLibres->random();
                        
                        // Cantidad aleatoria entre 1 y 50
                        $cantidad = rand(1, 50);
                        
                        // Fecha de vencimiento aleatoria (entre 30 días y 2 años)
                        $diasVencimiento = rand(30, 730);
                        $fechaVencimiento = now()->addDays($diasVencimiento);
                        
                        // Crear la ubicación del producto
                        ProductoUbicacion::create([
                            'producto_id' => $producto->id,
                            'ubicacion_id' => $ubicacion->id,
                            'cantidad' => $cantidad,
                            'fecha_ingreso' => now()->subDays(rand(1, 30)),
                            'fecha_vencimiento' => $fechaVencimiento,
                            'lote' => 'LOTE-' . date('Y') . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                            'observaciones' => 'Producto ubicado mediante seeder'
                        ]);

                        // Registrar movimiento de entrada
                        MovimientoStock::create([
                            'producto_id' => $producto->id,
                            'ubicacion_destino_id' => $ubicacion->id,
                            'tipo_movimiento' => 'entrada',
                            'cantidad' => $cantidad,
                            'motivo' => 'Carga inicial de datos',
                            'usuario_id' => 1, // Asumiendo que existe usuario ID 1
                            'datos_adicionales' => json_encode([
                                'lote' => 'LOTE-' . date('Y') . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                                'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                                'origen' => 'seeder'
                            ])
                        ]);

                        // Actualizar stock del producto
                        $producto->actualizarStockDesdeUbicaciones();
                        $producto->actualizarEstadoDesdeUbicaciones();
                        
                        echo "Producto {$producto->nombre} ubicado en {$estante->nombre} - {$ubicacion->codigo} con {$cantidad} unidades.\n";
                    }
                }

                // Crear algunos productos adicionales específicos para demostrar el sistema
                $productosDemo = [
                    [
                        'nombre' => 'Paracetamol 500mg Demo',
                        'codigo_barras' => '7891234567890',
                        'lote' => 'DEMO-001',
                        'categoria' => 'Analgésicos',
                        'marca' => 'Demo Pharma',
                        'presentacion' => 'Tabletas',
                        'concentracion' => '500mg',
                        'stock_actual' => 0,
                        'stock_minimo' => 10,
                        'ubicacion' => 'A-1-1',
                        'fecha_fabricacion' => now()->subYear(),
                        'fecha_vencimiento' => now()->addYear(),
                        'precio_compra' => 0.50,
                        'precio_venta' => 1.00,
                        'estado' => 'Normal'
                    ],
                    [
                        'nombre' => 'Vitamina C 1000mg Demo',
                        'codigo_barras' => '7891234567891',
                        'lote' => 'DEMO-002',
                        'categoria' => 'Vitaminas',
                        'marca' => 'Demo Vitamins',
                        'presentacion' => 'Cápsulas',
                        'concentracion' => '1000mg',
                        'stock_actual' => 0,
                        'stock_minimo' => 15,
                        'ubicacion' => 'B-2-3',
                        'fecha_fabricacion' => now()->subMonths(6),
                        'fecha_vencimiento' => now()->addDays(45), // Próximo a vencer
                        'precio_compra' => 2.00,
                        'precio_venta' => 4.00,
                        'estado' => 'Normal'
                    ]
                ];

                foreach ($productosDemo as $prodData) {
                    $prodDemo = Producto::create($prodData);
                    
                    // Ubicar estos productos específicos
                    $estanteDemo = $prodData['nombre'] === 'Paracetamol 500mg Demo' ? 
                                   $estantesCreados->where('nombre', 'Estante A')->first() :
                                   $estantesCreados->where('nombre', 'Estante B')->first();
                    
                    $ubicacionDemo = $estanteDemo->ubicaciones()->libres()->first();
                    
                    if ($ubicacionDemo) {
                        ProductoUbicacion::create([
                            'producto_id' => $prodDemo->id,
                            'ubicacion_id' => $ubicacionDemo->id,
                            'cantidad' => rand(20, 100),
                            'fecha_ingreso' => now()->subDays(15),
                            'fecha_vencimiento' => $prodData['fecha_vencimiento'],
                            'lote' => $prodData['lote'],
                            'observaciones' => 'Producto demo para testing'
                        ]);

                        $prodDemo->actualizarStockDesdeUbicaciones();
                        $prodDemo->actualizarEstadoDesdeUbicaciones();
                    }
                }
            }

            DB::commit();
            
            echo "\n✅ Seeder de almacén ejecutado exitosamente!\n";
            echo "📦 Estantes creados: " . Estante::count() . "\n";
            echo "📍 Ubicaciones creadas: " . Ubicacion::count() . "\n";
            echo "🏷️ Productos ubicados: " . ProductoUbicacion::count() . "\n";
            echo "📋 Movimientos registrados: " . MovimientoStock::count() . "\n";
            
        } catch (\Exception $e) {
            DB::rollBack();
            echo "❌ Error en el seeder: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}
