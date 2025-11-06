<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar notificaciones existentes
        Notification::truncate();
        
        // Crear notificaciones de prueba
        $notifications = [
            [
                'type' => Notification::TYPE_STOCK_CRITICO,
                'title' => 'Stock Crítico',
                'message' => 'Paracetamol 500mg - Solo quedan 5 unidades',
                'priority' => Notification::PRIORITY_URGENTE,
                'user_id' => 1,
                'created_at' => Carbon::now()->subMinutes(15),
                'data' => json_encode([
                    'producto_id' => 1,
                    'stock_actual' => 5,
                    'stock_minimo' => 10
                ])
            ],
            [
                'type' => Notification::TYPE_PRODUCTO_VENCIMIENTO,
                'title' => 'Próximo a Vencer',
                'message' => 'Ibuprofeno 400mg vence en 12 días',
                'priority' => Notification::PRIORITY_ADVERTENCIA,
                'user_id' => 1,
                'created_at' => Carbon::now()->subHour(1),
                'data' => json_encode([
                    'producto_id' => 2,
                    'fecha_vencimiento' => Carbon::now()->addDays(12)->format('Y-m-d'),
                    'dias_restantes' => 12
                ])
            ],
            [
                'type' => Notification::TYPE_STOCK_CRITICO,
                'title' => 'Stock Crítico',
                'message' => 'Amoxicilina 500mg - Solo quedan 3 unidades',
                'priority' => Notification::PRIORITY_URGENTE,
                'user_id' => 1,
                'created_at' => Carbon::now()->subHours(2),
                'data' => json_encode([
                    'producto_id' => 3,
                    'stock_actual' => 3,
                    'stock_minimo' => 15
                ])
            ],
            [
                'type' => Notification::TYPE_PRODUCTO_VENCIDO,
                'title' => 'Producto Vencido',
                'message' => 'Aspirina 100mg venció hace 2 días',
                'priority' => Notification::PRIORITY_URGENTE,
                'user_id' => 1,
                'created_at' => Carbon::now()->subHours(6),
                'data' => json_encode([
                    'producto_id' => 4,
                    'fecha_vencimiento' => Carbon::now()->subDays(2)->format('Y-m-d'),
                    'dias_vencido' => 2
                ])
            ],
            [
                'type' => Notification::TYPE_PRODUCTO_VENCIMIENTO,
                'title' => 'Próximo a Vencer',
                'message' => 'Loratadina 10mg vence en 5 días',
                'priority' => Notification::PRIORITY_ADVERTENCIA,
                'user_id' => 1,
                'created_at' => Carbon::now()->subHours(12),
                'data' => json_encode([
                    'producto_id' => 5,
                    'fecha_vencimiento' => Carbon::now()->addDays(5)->format('Y-m-d'),
                    'dias_restantes' => 5
                ])
            ]
        ];
        
        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
        
        $this->command->info('✅ Notificaciones de prueba creadas exitosamente');
        $this->command->info('📊 Total: ' . count($notifications) . ' notificaciones');
    }
}
