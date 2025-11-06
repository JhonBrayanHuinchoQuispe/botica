<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Paracetamol 500mg',
                'brand' => 'Genfar',
                'description' => 'Analgésico y antipirético para el alivio del dolor y la fiebre',
                'stock' => 50,
                'minStock' => 10,
                'expiryDate' => Carbon::now()->addMonths(8),
                'price' => 15.50,
                'costPrice' => 10.20,
                'barcode' => '7501234567890',
                'category' => 'Analgésicos',
                'laboratory' => 'Genfar',
                'batchNumber' => 'PAR240101'
            ],
            [
                'name' => 'Ibuprofeno 400mg',
                'brand' => 'Tecnoquímicas',
                'description' => 'Antiinflamatorio no esteroideo para el dolor y la inflamación',
                'stock' => 30,
                'minStock' => 15,
                'expiryDate' => Carbon::now()->addMonths(12),
                'price' => 18.75,
                'costPrice' => 12.50,
                'barcode' => '7501234567891',
                'category' => 'Antiinflamatorios',
                'laboratory' => 'Tecnoquímicas',
                'batchNumber' => 'IBU240102'
            ],
            [
                'name' => 'Amoxicilina 500mg',
                'brand' => 'Farmindustria',
                'description' => 'Antibiótico de amplio espectro para infecciones bacterianas',
                'stock' => 25,
                'minStock' => 12,
                'expiryDate' => Carbon::now()->addMonths(18),
                'price' => 25.90,
                'costPrice' => 18.60,
                'barcode' => '7501234567892',
                'category' => 'Antibióticos',
                'laboratory' => 'Farmindustria',
                'batchNumber' => 'AMO240103'
            ],
            [
                'name' => 'Vitamina C 1000mg',
                'brand' => 'Natura',
                'description' => 'Suplemento vitamínico para fortalecer el sistema inmunológico',
                'stock' => 8, // Stock bajo para demostrar alertas
                'minStock' => 10,
                'expiryDate' => Carbon::now()->addMonths(6),
                'price' => 22.50,
                'costPrice' => 15.80,
                'barcode' => '7501234567893',
                'category' => 'Vitaminas',
                'laboratory' => 'Natura',
                'batchNumber' => 'VIT240104'
            ],
            [
                'name' => 'Acetaminofén Jarabe',
                'brand' => 'Lafrancol',
                'description' => 'Analgésico y antipirético en presentación pediátrica',
                'stock' => 42,
                'minStock' => 8,
                'expiryDate' => Carbon::now()->addMonths(10),
                'price' => 12.30,
                'costPrice' => 8.90,
                'barcode' => '7501234567894',
                'category' => 'Pediátricos',
                'laboratory' => 'Lafrancol',
                'batchNumber' => 'ACE240105'
            ],
            [
                'name' => 'Loratadina 10mg',
                'brand' => 'MK',
                'description' => 'Antihistamínico para alergias y rinitis',
                'stock' => 35,
                'minStock' => 12,
                'expiryDate' => Carbon::now()->addMonths(15),
                'price' => 16.80,
                'costPrice' => 11.40,
                'barcode' => '7501234567895',
                'category' => 'Antihistamínicos',
                'laboratory' => 'MK',
                'batchNumber' => 'LOR240106'
            ],
            [
                'name' => 'Omeprazol 20mg',
                'brand' => 'Bagó',
                'description' => 'Inhibidor de la bomba de protones para acidez estomacal',
                'stock' => 28,
                'minStock' => 10,
                'expiryDate' => Carbon::now()->addMonths(14),
                'price' => 28.45,
                'costPrice' => 20.15,
                'barcode' => '7501234567896',
                'category' => 'Gastroenterología',
                'laboratory' => 'Bagó',
                'batchNumber' => 'OME240107'
            ],
            [
                'name' => 'Dipirona 500mg',
                'brand' => 'Sandoz',
                'description' => 'Analgésico y antipirético de acción rápida',
                'stock' => 2, // Stock muy bajo
                'minStock' => 8,
                'expiryDate' => Carbon::now()->addDays(20), // Próximo a vencer
                'price' => 14.20,
                'costPrice' => 9.80,
                'barcode' => '7501234567897',
                'category' => 'Analgésicos',
                'laboratory' => 'Sandoz',
                'batchNumber' => 'DIP240108'
            ],
            [
                'name' => 'Ácido Fólico 5mg',
                'brand' => 'Chalver',
                'description' => 'Suplemento vitamínico para embarazadas',
                'stock' => 60,
                'minStock' => 15,
                'expiryDate' => Carbon::now()->addMonths(20),
                'price' => 8.95,
                'costPrice' => 6.20,
                'barcode' => '7501234567898',
                'category' => 'Vitaminas',
                'laboratory' => 'Chalver',
                'batchNumber' => 'FOL240109'
            ],
            [
                'name' => 'Salbutamol Inhalador',
                'brand' => 'GSK',
                'description' => 'Broncodilatador para asma y enfermedades respiratorias',
                'stock' => 18,
                'minStock' => 5,
                'expiryDate' => Carbon::now()->addMonths(24),
                'price' => 45.60,
                'costPrice' => 32.80,
                'barcode' => '7501234567899',
                'category' => 'Respiratorios',
                'laboratory' => 'GSK',
                'batchNumber' => 'SAL240110'
            ],
            [
                'name' => 'Metformina 850mg',
                'brand' => 'Merck',
                'description' => 'Antidiabético para el control de la glucosa',
                'stock' => 45,
                'minStock' => 20,
                'expiryDate' => Carbon::now()->addMonths(16),
                'price' => 32.40,
                'costPrice' => 24.60,
                'barcode' => '7501234567800',
                'category' => 'Antidiabéticos',
                'laboratory' => 'Merck',
                'batchNumber' => 'MET240111'
            ],
            [
                'name' => 'Diclofenaco Gel',
                'brand' => 'Voltaren',
                'description' => 'Antiinflamatorio tópico para dolores musculares',
                'stock' => 22,
                'minStock' => 8,
                'expiryDate' => Carbon::now()->addMonths(11),
                'price' => 28.90,
                'costPrice' => 20.40,
                'barcode' => '7501234567801',
                'category' => 'Antiinflamatorios',
                'laboratory' => 'Novartis',
                'batchNumber' => 'DIC240112'
            ]
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        $this->command->info('Productos de ejemplo creados exitosamente!');
        $this->command->info('- Total productos: ' . count($products));
        $this->command->info('- Productos con stock bajo: ' . Product::lowStock()->count());
        $this->command->info('- Productos próximos a vencer: ' . Product::expiringSoon()->count());
    }
} 