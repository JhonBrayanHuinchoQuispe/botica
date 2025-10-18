<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Producto;
use App\Models\Notification;
use Carbon\Carbon;

class GenerarNotificaciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificaciones:generar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generar notificaciones para productos con stock bajo, agotados, próximos a vencer y vencidos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generando notificaciones...');
        
        // Primero verificar todos los productos
        $todosProductos = Producto::all();
        $this->info("Total de productos: {$todosProductos->count()}");
        
        foreach ($todosProductos as $producto) {
            $this->info("Producto: {$producto->nombre} - Stock: {$producto->stock_actual}/{$producto->stock_minimo} - Estado: {$producto->estado}");
        }
        
        // Verificar productos con stock bajo usando whereRaw
        $productosStockBajo = Producto::whereRaw('stock_actual <= stock_minimo')
            ->where('stock_actual', '>', 0)
            ->get();
            
        $this->info("Productos con stock bajo encontrados: {$productosStockBajo->count()}");
        
        foreach ($productosStockBajo as $producto) {
            // Verificar si ya existe una notificación reciente
            $existeNotificacion = Notification::where('type', Notification::TYPE_STOCK_CRITICO)
                ->where('data->producto_id', $producto->id)
                ->where('created_at', '>=', now()->subDay())
                ->exists();
                
            if (!$existeNotificacion) {
                Notification::createStockCritico(1, $producto);
                $this->info("✓ Notificación creada para: {$producto->nombre} (Stock: {$producto->stock_actual}/{$producto->stock_minimo})");
            } else {
                $this->warn("- Ya existe notificación para: {$producto->nombre}");
            }
        }
        
        // Verificar productos próximos a vencer
        $productosProximosVencer = Producto::whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [now()->addDay(), now()->addDays(30)])
            ->where('stock_actual', '>', 0)
            ->get();
            
        $this->info("Productos próximos a vencer encontrados: {$productosProximosVencer->count()}");
        
        foreach ($productosProximosVencer as $producto) {
            $diasRestantes = now()->diffInDays(Carbon::parse($producto->fecha_vencimiento), false);
            
            $existeNotificacion = Notification::where('type', Notification::TYPE_PRODUCTO_VENCIMIENTO)
                ->where('data->producto_id', $producto->id)
                ->where('created_at', '>=', now()->subDay())
                ->exists();
                
            if (!$existeNotificacion) {
                Notification::createProximoVencer(1, $producto, $diasRestantes);
                $this->info("✓ Notificación creada para: {$producto->nombre} (Vence en {$diasRestantes} días)");
            } else {
                $this->warn("- Ya existe notificación para: {$producto->nombre}");
            }
        }
        
        // Verificar productos agotados
        $productosAgotados = Producto::where('stock_actual', '<=', 0)->get();
        
        $this->info("Productos agotados encontrados: {$productosAgotados->count()}");
        
        foreach ($productosAgotados as $producto) {
            $existeNotificacion = Notification::where('type', Notification::TYPE_STOCK_AGOTADO)
                ->where('data->producto_id', $producto->id)
                ->where('created_at', '>=', now()->subDay())
                ->exists();
                
            if (!$existeNotificacion) {
                Notification::createStockAgotado(1, $producto);
                $this->info("✓ Notificación creada para: {$producto->nombre} (AGOTADO)");
            } else {
                $this->warn("- Ya existe notificación para: {$producto->nombre}");
            }
        }
        
        // Verificar productos vencidos
        $productosVencidos = Producto::whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now())
            ->where('stock_actual', '>', 0)
            ->get();
            
        $this->info("Productos vencidos encontrados: {$productosVencidos->count()}");
        
        foreach ($productosVencidos as $producto) {
            $existeNotificacion = Notification::where('type', Notification::TYPE_PRODUCTO_VENCIDO)
                ->where('data->producto_id', $producto->id)
                ->where('created_at', '>=', now()->subDay())
                ->exists();
                
            if (!$existeNotificacion) {
                Notification::createProductoVencido(1, $producto);
                $this->info("✓ Notificación creada para: {$producto->nombre} (VENCIDO)");
            } else {
                $this->warn("- Ya existe notificación para: {$producto->nombre}");
            }
        }
        
        $totalNotificaciones = Notification::count();
        $this->info("\nTotal de notificaciones en el sistema: {$totalNotificaciones}");
        
        $this->info('\n¡Proceso completado!');
        
        return 0;
    }
}