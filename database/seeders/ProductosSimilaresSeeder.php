<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use Carbon\Carbon;

class ProductosSimilaresSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🧪 Creando productos con funciones terapéuticas similares...');

        $productos = [
            // 🩹 GRUPO 1: ANALGÉSICOS Y ANTIINFLAMATORIOS (para dolor de cabeza, dolor muscular)
            [
                'nombre' => 'Ibuprofeno 400mg',
                'codigo_barras' => '7891234567901',
                'lote' => 'IBU2024001',
                'categoria' => 'Analgésicos',
                'marca' => 'Genfar',
                'presentacion' => 'Tabletas',
                'concentracion' => '400mg',
                'stock_actual' => 45,
                'stock_minimo' => 10,
                'ubicacion_almacen' => 'Estante A-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(2),
                'fecha_vencimiento' => Carbon::now()->addYears(3),
                'precio_compra' => 0.90,
                'precio_venta' => 2.50,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Naproxeno 250mg',
                'codigo_barras' => '7891234567902',
                'lote' => 'NAP2024002',
                'categoria' => 'Antiinflamatorios',
                'marca' => 'Bayer',
                'presentacion' => 'Cápsulas',
                'concentracion' => '250mg',
                'stock_actual' => 30,
                'stock_minimo' => 8,
                'ubicacion_almacen' => 'Estante A-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(1),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 1.20,
                'precio_venta' => 3.00,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Diclofenaco 50mg',
                'codigo_barras' => '7891234567903',
                'lote' => 'DIC2024003',
                'categoria' => 'AINES',
                'marca' => 'Voltaren',
                'presentacion' => 'Tabletas',
                'concentracion' => '50mg',
                'stock_actual' => 25,
                'stock_minimo' => 5,
                'ubicacion_almacen' => 'Estante A-3',
                'fecha_fabricacion' => Carbon::now()->subMonths(3),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 1.50,
                'precio_venta' => 4.00,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Acetaminofén 500mg',
                'codigo_barras' => '7891234567904',
                'lote' => 'ACE2024004',
                'categoria' => 'Antipiréticos',
                'marca' => 'Tylenol',
                'presentacion' => 'Tabletas',
                'concentracion' => '500mg',
                'stock_actual' => 60,
                'stock_minimo' => 15,
                'ubicacion_almacen' => 'Estante A-4',
                'fecha_fabricacion' => Carbon::now()->subMonths(1),
                'fecha_vencimiento' => Carbon::now()->addYears(3),
                'precio_compra' => 0.70,
                'precio_venta' => 1.80,
                'imagen' => null,
                'estado' => 'Normal'
            ],

            // 🦠 GRUPO 2: ANTIBIÓTICOS (para infecciones)
            [
                'nombre' => 'Azitromicina 500mg',
                'codigo_barras' => '7891234567905',
                'lote' => 'AZI2024005',
                'categoria' => 'Antibióticos',
                'marca' => 'Pfizer',
                'presentacion' => 'Tabletas',
                'concentracion' => '500mg',
                'stock_actual' => 20,
                'stock_minimo' => 5,
                'ubicacion_almacen' => 'Estante B-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(2),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 3.50,
                'precio_venta' => 8.00,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Ciprofloxacino 500mg',
                'codigo_barras' => '7891234567906',
                'lote' => 'CIP2024006',
                'categoria' => 'Antimicrobianos',
                'marca' => 'Bayer',
                'presentacion' => 'Tabletas',
                'concentracion' => '500mg',
                'stock_actual' => 15,
                'stock_minimo' => 3,
                'ubicacion_almacen' => 'Estante B-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(1),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 2.80,
                'precio_venta' => 6.50,
                'imagen' => null,
                'estado' => 'Normal'
            ],

            // 🤧 GRUPO 3: ANTIHISTAMÍNICOS (para alergias)
            [
                'nombre' => 'Loratadina 10mg',
                'codigo_barras' => '7891234567907',
                'lote' => 'LOR2024007',
                'categoria' => 'Antihistamínicos',
                'marca' => 'Claritin',
                'presentacion' => 'Tabletas',
                'concentracion' => '10mg',
                'stock_actual' => 40,
                'stock_minimo' => 10,
                'ubicacion_almacen' => 'Estante C-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(2),
                'fecha_vencimiento' => Carbon::now()->addYears(3),
                'precio_compra' => 1.00,
                'precio_venta' => 2.80,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Cetirizina 10mg',
                'codigo_barras' => '7891234567908',
                'lote' => 'CET2024008',
                'categoria' => 'Antialérgicos',
                'marca' => 'Zyrtec',
                'presentacion' => 'Tabletas',
                'concentracion' => '10mg',
                'stock_actual' => 35,
                'stock_minimo' => 8,
                'ubicacion_almacen' => 'Estante C-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(1),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 1.20,
                'precio_venta' => 3.20,
                'imagen' => null,
                'estado' => 'Normal'
            ],

            // 💊 GRUPO 4: VITAMINAS Y SUPLEMENTOS
            [
                'nombre' => 'Vitamina D3 1000 UI',
                'codigo_barras' => '7891234567909',
                'lote' => 'VD32024009',
                'categoria' => 'Vitaminas',
                'marca' => 'Nature Made',
                'presentacion' => 'Cápsulas',
                'concentracion' => '1000 UI',
                'stock_actual' => 80,
                'stock_minimo' => 20,
                'ubicacion_almacen' => 'Estante D-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(1),
                'fecha_vencimiento' => Carbon::now()->addYears(3),
                'precio_compra' => 2.00,
                'precio_venta' => 5.50,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Complejo B',
                'codigo_barras' => '7891234567910',
                'lote' => 'CB2024010',
                'categoria' => 'Suplementos',
                'marca' => 'Centrum',
                'presentacion' => 'Tabletas',
                'concentracion' => 'Multivitamínico',
                'stock_actual' => 50,
                'stock_minimo' => 12,
                'ubicacion_almacen' => 'Estante D-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(2),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 3.00,
                'precio_venta' => 7.50,
                'imagen' => null,
                'estado' => 'Normal'
            ],

            // 🫀 GRUPO 5: CARDIOVASCULARES
            [
                'nombre' => 'Enalapril 10mg',
                'codigo_barras' => '7891234567911',
                'lote' => 'ENA2024011',
                'categoria' => 'Antihipertensivos',
                'marca' => 'Genfar',
                'presentacion' => 'Tabletas',
                'concentracion' => '10mg',
                'stock_actual' => 30,
                'stock_minimo' => 8,
                'ubicacion_almacen' => 'Estante E-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(3),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 1.80,
                'precio_venta' => 4.50,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Losartán 50mg',
                'codigo_barras' => '7891234567912',
                'lote' => 'LOS2024012',
                'categoria' => 'Cardiovasculares',
                'marca' => 'Merck',
                'presentacion' => 'Tabletas',
                'concentracion' => '50mg',
                'stock_actual' => 25,
                'stock_minimo' => 6,
                'ubicacion_almacen' => 'Estante E-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(2),
                'fecha_vencimiento' => Carbon::now()->addYears(3),
                'precio_compra' => 2.20,
                'precio_venta' => 5.80,
                'imagen' => null,
                'estado' => 'Normal'
            ],

            // 🍯 GRUPO 6: DIGESTIVOS
            [
                'nombre' => 'Omeprazol 20mg',
                'codigo_barras' => '7891234567913',
                'lote' => 'OME2024013',
                'categoria' => 'Gastroprotectores',
                'marca' => 'Prilosec',
                'presentacion' => 'Cápsulas',
                'concentracion' => '20mg',
                'stock_actual' => 40,
                'stock_minimo' => 10,
                'ubicacion_almacen' => 'Estante F-1',
                'fecha_fabricacion' => Carbon::now()->subMonths(1),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 1.50,
                'precio_venta' => 4.00,
                'imagen' => null,
                'estado' => 'Normal'
            ],
            [
                'nombre' => 'Ranitidina 150mg',
                'codigo_barras' => '7891234567914',
                'lote' => 'RAN2024014',
                'categoria' => 'Digestivos',
                'marca' => 'Zantac',
                'presentacion' => 'Tabletas',
                'concentracion' => '150mg',
                'stock_actual' => 20,
                'stock_minimo' => 5,
                'ubicacion_almacen' => 'Estante F-2',
                'fecha_fabricacion' => Carbon::now()->subMonths(2),
                'fecha_vencimiento' => Carbon::now()->addYears(2),
                'precio_compra' => 1.20,
                'precio_venta' => 3.50,
                'imagen' => null,
                'estado' => 'Normal'
            ]
        ];

        foreach ($productos as $productoData) {
            // Verificar si ya existe por código de barras
            $existente = Producto::where('codigo_barras', $productoData['codigo_barras'])->first();
            
            if (!$existente) {
                Producto::create($productoData);
                $this->command->info("✅ Creado: {$productoData['nombre']} - {$productoData['categoria']}");
            } else {
                $this->command->warn("⚠️  Ya existe: {$productoData['nombre']}");
            }
        }

        $this->command->info('🎯 ¡Productos similares creados exitosamente!');
        $this->command->info('');
        $this->command->info('📋 GRUPOS CREADOS PARA PROBAR ALTERNATIVAS:');
        $this->command->info('🩹 Analgésicos: Ibuprofeno, Naproxeno, Diclofenaco, Acetaminofén');
        $this->command->info('🦠 Antibióticos: Azitromicina, Ciprofloxacino');
        $this->command->info('🤧 Antihistamínicos: Loratadina, Cetirizina');
        $this->command->info('💊 Vitaminas: Vitamina D3, Complejo B');
        $this->command->info('🫀 Cardiovasculares: Enalapril, Losartán');
        $this->command->info('🍯 Digestivos: Omeprazol, Ranitidina');
        $this->command->info('');
        $this->command->info('🧪 Ahora puedes buscar cualquiera de estos productos y ver las alternativas!');
    }
}