<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PuntoVenta\Cliente;
use App\Models\Producto;

class PuntoVentaSeeder extends Seeder
{
    public function run(): void
    {
        // Crear algunos clientes de prueba
        $clientes = [
            [
                'dni' => '12345678',
                'nombres' => 'Juan Carlos',
                'apellido_paterno' => 'García',
                'apellido_materno' => 'López',
                'activo' => true
            ],
            [
                'dni' => '87654321',
                'nombres' => 'María Elena',
                'apellido_paterno' => 'Rodríguez',
                'apellido_materno' => 'Pérez',
                'activo' => true
            ],
            [
                'dni' => '11223344',
                'nombres' => 'Pedro Antonio',
                'apellido_paterno' => 'Martínez',
                'apellido_materno' => 'Silva',
                'activo' => true
            ],
            [
                'dni' => '44332211',
                'nombres' => 'Ana Sofía',
                'apellido_paterno' => 'Fernández',
                'apellido_materno' => 'Torres',
                'activo' => true
            ],
            [
                'dni' => '55667788',
                'nombres' => 'Carlos Eduardo',
                'apellido_paterno' => 'Vargas',
                'apellido_materno' => 'Mendoza',
                'activo' => true
            ]
        ];

        foreach ($clientes as $clienteData) {
            Cliente::updateOrCreate(
                ['dni' => $clienteData['dni']],
                $clienteData
            );
        }

        $this->command->info('✅ Clientes de prueba creados exitosamente');

        // Verificar que existan algunos productos
        $productosCount = Producto::count();
        
        if ($productosCount === 0) {
            $this->command->warn('⚠️  No hay productos en la base de datos. Creando algunos productos de prueba...');
            
            // Crear algunos productos de prueba si no existen
            $productos = [
                [
                    'nombre' => 'Paracetamol 500mg',
                    'concentracion' => '500mg',
                    'categoria' => 'Analgésicos',
                    'marca' => 'Genfar',
                    'presentacion' => 'Tabletas',
                    'lote' => 'PAR001',
                    'codigo_barras' => '7890123456781',
                    'stock_actual' => 100,
                    'stock_minimo' => 10,
                    'precio_compra' => 0.50,
                    'precio_venta' => 0.80,
                    'fecha_vencimiento' => now()->addMonths(18),
                    'ubicacion' => 'Estante A1',
                    'estado' => 'Normal'
                ],
                [
                    'nombre' => 'Ibuprofeno 400mg',
                    'concentracion' => '400mg',
                    'categoria' => 'Antiinflamatorios',
                    'marca' => 'Bayer',
                    'presentacion' => 'Cápsulas',
                    'lote' => 'IBU002',
                    'codigo_barras' => '7890123456782',
                    'stock_actual' => 75,
                    'stock_minimo' => 15,
                    'precio_compra' => 1.20,
                    'precio_venta' => 1.80,
                    'fecha_vencimiento' => now()->addMonths(20),
                    'ubicacion' => 'Estante A2',
                    'estado' => 'Normal'
                ],
                [
                    'nombre' => 'Amoxicilina 500mg',
                    'concentracion' => '500mg',
                    'categoria' => 'Antibióticos',
                    'marca' => 'Pfizer',
                    'presentacion' => 'Cápsulas',
                    'lote' => 'AMX003',
                    'codigo_barras' => '7890123456783',
                    'stock_actual' => 50,
                    'stock_minimo' => 20,
                    'precio_compra' => 2.50,
                    'precio_venta' => 3.50,
                    'fecha_vencimiento' => now()->addMonths(24),
                    'ubicacion' => 'Estante B1',
                    'estado' => 'Normal'
                ],
                [
                    'nombre' => 'Vitamina C 1000mg',
                    'concentracion' => '1000mg',
                    'categoria' => 'Vitaminas',
                    'marca' => 'Nature\'s Bounty',
                    'presentacion' => 'Tabletas',
                    'lote' => 'VTC004',
                    'codigo_barras' => '7890123456784',
                    'stock_actual' => 80,
                    'stock_minimo' => 12,
                    'precio_compra' => 1.80,
                    'precio_venta' => 2.50,
                    'fecha_vencimiento' => now()->addMonths(30),
                    'ubicacion' => 'Estante C1',
                    'estado' => 'Normal'
                ],
                [
                    'nombre' => 'Loratadina 10mg',
                    'concentracion' => '10mg',
                    'categoria' => 'Antihistamínicos',
                    'marca' => 'Schering-Plough',
                    'presentacion' => 'Tabletas',
                    'lote' => 'LOR005',
                    'codigo_barras' => '7890123456785',
                    'stock_actual' => 60,
                    'stock_minimo' => 18,
                    'precio_compra' => 0.90,
                    'precio_venta' => 1.40,
                    'fecha_vencimiento' => now()->addMonths(22),
                    'ubicacion' => 'Estante A3',
                    'estado' => 'Normal'
                ]
            ];

            foreach ($productos as $productoData) {
                Producto::create($productoData);
            }
            
            $this->command->info('✅ Productos de prueba creados exitosamente');
        } else {
            $this->command->info("✅ Se encontraron {$productosCount} productos existentes en la base de datos");
        }

                 $this->command->info('🎉 Seeder del Punto de Venta completado exitosamente');
        $this->command->info('📝 Datos creados:');
        $this->command->info('   - ' . Cliente::count() . ' clientes');
        $this->command->info('   - ' . Producto::count() . ' productos disponibles');
        $this->command->info('');
        $this->command->info('🔑 Para probar el sistema, usa estos DNIs de clientes:');
        foreach ($clientes as $cliente) {
            $this->command->info("   - {$cliente['dni']}: {$cliente['nombres']} {$cliente['apellido_paterno']} {$cliente['apellido_materno']}");
        }
    }
}