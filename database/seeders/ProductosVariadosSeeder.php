<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use Carbon\Carbon;

class ProductosVariadosSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🏥 Creando 20 productos variados con diferentes estados...');

        $productos = [
            // 1. PRODUCTOS NORMALES (Stock adecuado, no vencidos)
            [
                'nombre' => 'Paracetamol 500mg',
                'codigo_barras' => '7891000002001',
                'lote' => 'PAR2024001',
                'categoria' => 'Analgésicos',
                'marca' => 'Genfar',
                'presentacion' => 'Tabletas',
                'concentracion' => '500mg',
                'stock_actual' => 150,
                'stock_minimo' => 20,
                'ubicacion_almacen' => 'Estante A-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(3),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 0.80,
                'precio_venta' => 2.50,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Amoxicilina 500mg',
                'codigo_barras' => '7891000002002',
                'lote' => 'AMX2024002',
                'categoria' => 'Antibióticos',
                'marca' => 'Farmindustria',
                'presentacion' => 'Cápsulas',
                'concentracion' => '500mg',
                'stock_actual' => 80,
                'stock_minimo' => 15,
                'ubicacion_almacen' => 'Estante B-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(2),
                'fecha_vencimiento' => Carbon::now()->addMonths(18),
                'precio_compra' => 1.20,
                'precio_venta' => 3.80,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Vitamina C 1000mg',
                'codigo_barras' => '7891000002003',
                'lote' => 'VTC2024003',
                'categoria' => 'Vitaminas',
                'marca' => 'Centrum',
                'presentacion' => 'Tabletas',
                'concentracion' => '1000mg',
                'stock_actual' => 120,
                'stock_minimo' => 25,
                'ubicacion_almacen' => 'Estante C-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(1),
                'fecha_vencimiento' => Carbon::now()->addYears(3),
                'precio_compra' => 2.50,
                'precio_venta' => 6.00,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Omeprazol 20mg',
                'codigo_barras' => '7891000002004',
                'lote' => 'OME2024004',
                'categoria' => 'Gastroenterología',
                'marca' => 'Bagó',
                'presentacion' => 'Cápsulas',
                'concentracion' => '20mg',
                'stock_actual' => 95,
                'stock_minimo' => 20,
                'ubicacion_almacen' => 'Estante D-3',
                'fecha_fabricacion' => Carbon::now()->subMonths(4),
                'fecha_vencimiento' => Carbon::now()->addMonths(20),
                'precio_compra' => 1.80,
                'precio_venta' => 4.50,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Loratadina 10mg',
                'codigo_barras' => '7891000002005',
                'lote' => 'LOR2024005',
                'categoria' => 'Antihistamínicos',
                'marca' => 'MK',
                'presentacion' => 'Tabletas',
                'concentracion' => '10mg',
                'stock_actual' => 65,
                'stock_minimo' => 12,
                'ubicacion_almacen' => 'Estante E-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(2),
                'fecha_vencimiento' => Carbon::now()->addMonths(24),
                'precio_compra' => 0.90,
                'precio_venta' => 2.80,
                'estado' => 'Normal'
            ],

            // 2. PRODUCTOS CON BAJO STOCK
            [
                'nombre' => 'Ibuprofeno 400mg',
                'codigo_barras' => '7891000002006',
                'lote' => 'IBU2024006',
                'categoria' => 'Antiinflamatorios',
                'marca' => 'Bayer',
                'presentacion' => 'Tabletas',
                'concentracion' => '400mg',
                'stock_actual' => 8, // Bajo stock
                'stock_minimo' => 15,
                'ubicacion_almacen' => 'Estante A-3',
                'fecha_fabricacion' => Carbon::now()->subMonths(3),
                'fecha_vencimiento' => Carbon::now()->addMonths(15),
                'precio_compra' => 1.00,
                'precio_venta' => 3.20,
                'estado' => 'Bajo stock'
            ],
            [
                'nombre' => 'Acetaminofén Jarabe',
                'codigo_barras' => '7891000002007',
                'lote' => 'ACE2024007',
                'categoria' => 'Pediátricos',
                'marca' => 'Lafrancol',
                'presentacion' => 'Jarabe',
                'concentracion' => '160mg/5ml',
                'stock_actual' => 5, // Bajo stock
                'stock_minimo' => 12,
                'ubicacion_almacen' => 'Estante F-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(2),
                'fecha_vencimiento' => Carbon::now()->addMonths(18),
                'precio_compra' => 3.50,
                'precio_venta' => 8.90,
                'estado' => 'Bajo stock'
            ],
            [
                'nombre' => 'Salbutamol Inhalador',
                'codigo_barras' => '7891000002008',
                'lote' => 'SAL2024008',
                'categoria' => 'Respiratorios',
                'marca' => 'GSK',
                'presentacion' => 'Inhalador',
                'concentracion' => '100mcg/dosis',
                'stock_actual' => 3, // Muy bajo stock
                'stock_minimo' => 8,
                'ubicacion_almacen' => 'Estante G-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(1),
                'fecha_vencimiento' => Carbon::now()->addMonths(12),
                'precio_compra' => 12.00,
                'precio_venta' => 28.50,
                'estado' => 'Bajo stock'
            ],
            [
                'nombre' => 'Diclofenaco Gel',
                'codigo_barras' => '7891000002009',
                'lote' => 'DIC2024009',
                'categoria' => 'Tópicos',
                'marca' => 'Voltaren',
                'presentacion' => 'Gel',
                'concentracion' => '1%',
                'stock_actual' => 6, // Bajo stock
                'stock_minimo' => 10,
                'ubicacion_almacen' => 'Estante H-3',
                'fecha_fabricacion' => Carbon::now()->subMonths(4),
                'fecha_vencimiento' => Carbon::now()->addMonths(16),
                'precio_compra' => 8.50,
                'precio_venta' => 18.90,
                'estado' => 'Bajo stock'
            ],

            // 3. PRODUCTOS PRÓXIMOS A VENCER (menos de 3 meses)
            [
                'nombre' => 'Dipirona 500mg',
                'codigo_barras' => '7891000002010',
                'lote' => 'DIP2024010',
                'categoria' => 'Analgésicos',
                'marca' => 'Sandoz',
                'presentacion' => 'Tabletas',
                'concentracion' => '500mg',
                'stock_actual' => 45,
                'stock_minimo' => 15,
                'ubicacion_almacen' => 'Estante A-4',
                'fecha_fabricacion' => Carbon::now()->subMonths(18),
                'fecha_vencimiento' => Carbon::now()->addDays(45), // Próximo a vencer
                'precio_compra' => 0.70,
                'precio_venta' => 2.20,
                'estado' => 'Por vencer'
            ],
            [
                'nombre' => 'Ácido Fólico 5mg',
                'codigo_barras' => '7891000002011',
                'lote' => 'FOL2024011',
                'categoria' => 'Vitaminas',
                'marca' => 'Chalver',
                'presentacion' => 'Tabletas',
                'concentracion' => '5mg',
                'stock_actual' => 30,
                'stock_minimo' => 10,
                'ubicacion_almacen' => 'Estante C-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(20),
                'fecha_vencimiento' => Carbon::now()->addDays(60), // Próximo a vencer
                'precio_compra' => 1.20,
                'precio_venta' => 3.50,
                'estado' => 'Por vencer'
            ],
            [
                'nombre' => 'Cetirizina 10mg',
                'codigo_barras' => '7891000002012',
                'lote' => 'CET2024012',
                'categoria' => 'Antihistamínicos',
                'marca' => 'Zyrtec',
                'presentacion' => 'Tabletas',
                'concentracion' => '10mg',
                'stock_actual' => 25,
                'stock_minimo' => 8,
                'ubicacion_almacen' => 'Estante E-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(22),
                'fecha_vencimiento' => Carbon::now()->addDays(75), // Próximo a vencer
                'precio_compra' => 1.50,
                'precio_venta' => 4.20,
                'estado' => 'Por vencer'
            ],

            // 4. PRODUCTOS VENCIDOS
            [
                'nombre' => 'Aspirina 100mg',
                'codigo_barras' => '7891000002013',
                'lote' => 'ASP2023013',
                'categoria' => 'Analgésicos',
                'marca' => 'Bayer',
                'presentacion' => 'Tabletas',
                'concentracion' => '100mg',
                'stock_actual' => 20,
                'stock_minimo' => 10,
                'ubicacion_almacen' => 'Estante A-5',
                'fecha_fabricacion' => Carbon::now()->subMonths(30),
                'fecha_vencimiento' => Carbon::now()->subDays(15), // Vencido
                'precio_compra' => 0.60,
                'precio_venta' => 1.80,
                'estado' => 'Vencido'
            ],
            [
                'nombre' => 'Metamizol 500mg',
                'codigo_barras' => '7891000002014',
                'lote' => 'MET2023014',
                'categoria' => 'Analgésicos',
                'marca' => 'Novalgina',
                'presentacion' => 'Tabletas',
                'concentracion' => '500mg',
                'stock_actual' => 15,
                'stock_minimo' => 8,
                'ubicacion_almacen' => 'Estante A-6',
                'fecha_fabricacion' => Carbon::now()->subMonths(28),
                'fecha_vencimiento' => Carbon::now()->subDays(30), // Vencido
                'precio_compra' => 0.80,
                'precio_venta' => 2.40,
                'estado' => 'Vencido'
            ],
            [
                'nombre' => 'Complejo B',
                'codigo_barras' => '7891000002015',
                'lote' => 'CPB2023015',
                'categoria' => 'Vitaminas',
                'marca' => 'Bedoyecta',
                'presentacion' => 'Cápsulas',
                'concentracion' => 'Multivitamínico',
                'stock_actual' => 12,
                'stock_minimo' => 5,
                'ubicacion_almacen' => 'Estante C-3',
                'fecha_fabricacion' => Carbon::now()->subMonths(32),
                'fecha_vencimiento' => Carbon::now()->subDays(45), // Vencido
                'precio_compra' => 2.80,
                'precio_venta' => 7.50,
                'estado' => 'Vencido'
            ],

            // 5. PRODUCTOS ADICIONALES NORMALES
            [
                'nombre' => 'Ranitidina 150mg',
                'codigo_barras' => '7891000002016',
                'lote' => 'RAN2024016',
                'categoria' => 'Gastroenterología',
                'marca' => 'Zantac',
                'presentacion' => 'Tabletas',
                'concentracion' => '150mg',
                'stock_actual' => 70,
                'stock_minimo' => 15,
                'ubicacion_almacen' => 'Estante D-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(2),
                'fecha_vencimiento' => Carbon::now()->addMonths(22),
                'precio_compra' => 1.40,
                'precio_venta' => 3.90,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Ketorolaco 10mg',
                'codigo_barras' => '7891000002017',
                'lote' => 'KET2024017',
                'categoria' => 'Antiinflamatorios',
                'marca' => 'Dolac',
                'presentacion' => 'Tabletas',
                'concentracion' => '10mg',
                'stock_actual' => 55,
                'stock_minimo' => 12,
                'ubicacion_almacen' => 'Estante B-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(3),
                'fecha_vencimiento' => Carbon::now()->addMonths(21),
                'precio_compra' => 1.60,
                'precio_venta' => 4.80,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Clotrimazol Crema',
                'codigo_barras' => '7891000002018',
                'lote' => 'CLO2024018',
                'categoria' => 'Dermatológicos',
                'marca' => 'Canesten',
                'presentacion' => 'Crema',
                'concentracion' => '1%',
                'stock_actual' => 35,
                'stock_minimo' => 8,
                'ubicacion_almacen' => 'Estante I-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(1),
                'fecha_vencimiento' => Carbon::now()->addMonths(24),
                'precio_compra' => 6.50,
                'precio_venta' => 15.90,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Simvastatina 20mg',
                'codigo_barras' => '7891000002019',
                'lote' => 'SIM2024019',
                'categoria' => 'Cardiovasculares',
                'marca' => 'Zocor',
                'presentacion' => 'Tabletas',
                'concentracion' => '20mg',
                'stock_actual' => 40,
                'stock_minimo' => 10,
                'ubicacion_almacen' => 'Estante J-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(2),
                'fecha_vencimiento' => Carbon::now()->addMonths(30),
                'precio_compra' => 2.20,
                'precio_venta' => 6.80,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Hidrocortisona Crema',
                'codigo_barras' => '7891000002020',
                'lote' => 'HID2024020',
                'categoria' => 'Dermatológicos',
                'marca' => 'Cortaid',
                'presentacion' => 'Crema',
                'concentracion' => '1%',
                'stock_actual' => 28,
                'stock_minimo' => 6,
                'ubicacion_almacen' => 'Estante I-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(1),
                'fecha_vencimiento' => Carbon::now()->addMonths(18),
                'precio_compra' => 4.80,
                'precio_venta' => 12.50,
                'estado' => 'Normal'
            ]
        ];

        // Crear los productos
        foreach ($productos as $producto) {
            Producto::create($producto);
        }

        // Mostrar resumen
        $this->command->info('✅ ¡20 productos creados exitosamente!');
        $this->command->info('📊 Resumen por estado:');
        $this->command->info('   • Normal: 10 productos');
        $this->command->info('   • Bajo stock: 4 productos');
        $this->command->info('   • Por vencer: 3 productos');
        $this->command->info('   • Vencido: 3 productos');
        $this->command->info('');
        $this->command->info('🏷️ Categorías incluidas:');
        $this->command->info('   • Analgésicos, Antibióticos, Vitaminas');
        $this->command->info('   • Gastroenterología, Antihistamínicos');
        $this->command->info('   • Antiinflamatorios, Pediátricos');
        $this->command->info('   • Respiratorios, Tópicos, Dermatológicos');
        $this->command->info('   • Cardiovasculares');
    }
}