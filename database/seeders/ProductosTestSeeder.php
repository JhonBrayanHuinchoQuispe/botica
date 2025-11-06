<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use Carbon\Carbon;

class ProductosTestSeeder extends Seeder
{
    public function run()
    {
        // Limpiar productos existentes (opcional)
        // Producto::truncate();

        $productos = [
            [
                'nombre' => 'Paracetamol 500mg',
                'codigo_barras' => '7891234567890',
                'lote' => 'LOTE001',
                'categoria' => 'Analgésicos',
                'marca' => 'Genérico',
                'presentacion' => 'Tabletas',
                'concentracion' => '500mg',
                'stock_actual' => 100,
                'stock_minimo' => 20,
                'ubicacion' => 'Estante A-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(6),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 0.50,
                'precio_venta' => 1.50,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Ibuprofeno 400mg',
                'codigo_barras' => '7891234567891',
                'lote' => 'LOTE002',
                'categoria' => 'Antiinflamatorios',
                'marca' => 'Genérico',
                'presentacion' => 'Tabletas',
                'concentracion' => '400mg',
                'stock_actual' => 75,
                'stock_minimo' => 15,
                'ubicacion' => 'Estante A-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(4),
                'fecha_vencimiento' => Carbon::now()->addYears(3),
                'precio_compra' => 0.80,
                'precio_venta' => 2.00,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Aspirina 100mg',
                'codigo_barras' => '7891234567892',
                'lote' => 'LOTE003',
                'categoria' => 'Anticoagulantes',
                'marca' => 'Bayer',
                'presentacion' => 'Tabletas',
                'concentracion' => '100mg',
                'stock_actual' => 50,
                'stock_minimo' => 10,
                'ubicacion' => 'Estante B-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(3),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 1.20,
                'precio_venta' => 3.00,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Omeprazol 20mg',
                'codigo_barras' => '7891234567893',
                'lote' => 'LOTE004',
                'categoria' => 'Antiácidos',
                'marca' => 'Genérico',
                'presentacion' => 'Cápsulas',
                'concentracion' => '20mg',
                'stock_actual' => 60,
                'stock_minimo' => 12,
                'ubicacion' => 'Estante B-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(5),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 1.50,
                'precio_venta' => 3.50,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Amoxicilina 500mg',
                'codigo_barras' => '7891234567894',
                'lote' => 'LOTE005',
                'categoria' => 'Antibióticos',
                'marca' => 'Genérico',
                'presentacion' => 'Cápsulas',
                'concentracion' => '500mg',
                'stock_actual' => 40,
                'stock_minimo' => 8,
                'ubicacion' => 'Estante C-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(2),
                'fecha_vencimiento' => Carbon::now()->addYears(1),
                'precio_compra' => 2.00,
                'precio_venta' => 4.50,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Loratadina 10mg',
                'codigo_barras' => '7891234567895',
                'lote' => 'LOTE006',
                'categoria' => 'Antihistamínicos',
                'marca' => 'Genérico',
                'presentacion' => 'Tabletas',
                'concentracion' => '10mg',
                'stock_actual' => 80,
                'stock_minimo' => 16,
                'ubicacion' => 'Estante C-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(1),
                'fecha_vencimiento' => Carbon::now()->addYears(3),
                'precio_compra' => 0.60,
                'precio_venta' => 1.80,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Vitamina C 1000mg',
                'codigo_barras' => '7891234567896',
                'lote' => 'LOTE007',
                'categoria' => 'Vitaminas',
                'marca' => 'Natures Bounty',
                'presentacion' => 'Tabletas',
                'concentracion' => '1000mg',
                'stock_actual' => 120,
                'stock_minimo' => 24,
                'ubicacion' => 'Estante D-1',
                'fecha_fabricacion' => Carbon::now()->subWeeks(2),
                'fecha_vencimiento' => Carbon::now()->addYears(4),
                'precio_compra' => 1.00,
                'precio_venta' => 2.50,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Simvastatina 20mg',
                'codigo_barras' => '7891234567897',
                'lote' => 'LOTE008',
                'categoria' => 'Cardiovasculares',
                'marca' => 'Genérico',
                'presentacion' => 'Tabletas',
                'concentracion' => '20mg',
                'stock_actual' => 35,
                'stock_minimo' => 7,
                'ubicacion' => 'Estante D-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(7),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 1.80,
                'precio_venta' => 4.00,
                'imagen' => null,
                'estado' => 'Normal'
            ]
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }

        $this->command->info('✅ Productos de prueba creados exitosamente para el punto de venta!');
        $this->command->info('📦 Total productos creados: ' . count($productos));
    }
} 