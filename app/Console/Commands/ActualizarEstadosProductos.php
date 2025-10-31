<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Producto;
use Carbon\Carbon;

class ActualizarEstadosProductos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'productos:actualizar-estados {--force : Forzar actualización sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza los estados de todos los productos basado en stock y fechas de vencimiento';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando actualización de estados de productos...');
        
        $productos = Producto::all();
        $actualizados = 0;
        $errores = 0;
        
        $this->info("📦 Total de productos a revisar: {$productos->count()}");
        
        if (!$this->option('force')) {
            if (!$this->confirm('¿Desea continuar con la actualización?')) {
                $this->info('❌ Operación cancelada.');
                return 0;
            }
        }
        
        $progressBar = $this->output->createProgressBar($productos->count());
        $progressBar->start();
        
        foreach ($productos as $producto) {
            try {
                $estadoAnterior = $producto->estado;
                $nuevoEstado = $this->calcularEstado($producto);
                
                if ($estadoAnterior !== $nuevoEstado) {
                    $producto->update(['estado' => $nuevoEstado]);
                    $actualizados++;
                    
                    if ($this->output->isVerbose()) {
                        $this->line("\n📝 {$producto->nombre}: '{$estadoAnterior}' → '{$nuevoEstado}'");
                    }
                }
                
            } catch (\Exception $e) {
                $errores++;
                $this->error("\n❌ Error actualizando {$producto->nombre}: {$e->getMessage()}");
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        
        $this->newLine(2);
        $this->info("✅ Actualización completada:");
        $this->info("   📊 Productos revisados: {$productos->count()}");
        $this->info("   🔄 Productos actualizados: {$actualizados}");
        $this->info("   ❌ Errores: {$errores}");
        
        // Mostrar estadísticas finales
        $this->mostrarEstadisticas();
        
        return 0;
    }
    
    /**
     * Calcular el estado de un producto
     */
    private function calcularEstado(Producto $producto): string
    {
        $fechaActual = Carbon::now('America/Lima');
        
        // 1. Verificar si está vencido
        if ($producto->fecha_vencimiento && $fechaActual->gt($producto->fecha_vencimiento)) {
            return 'Vencido';
        }
        
        // 2. Verificar si está agotado (stock 0)
        if ($producto->stock_actual <= 0) {
            return 'Agotado';
        }
        
        // 3. Verificar si está próximo a vencer (30 días) - tiene prioridad sobre stock bajo
        if ($producto->fecha_vencimiento) {
            $diasParaVencer = $fechaActual->diffInDays($producto->fecha_vencimiento, false);
            if ($diasParaVencer <= 30 && $diasParaVencer > 0) {
                return 'Por vencer';
            }
        }
        
        // 4. Verificar si tiene stock bajo (mayor a 0 pero menor o igual al mínimo)
        if ($producto->stock_actual <= $producto->stock_minimo) {
            return 'Bajo stock';
        }
        
        // 5. Estado normal
        return 'Normal';
    }
    
    /**
     * Mostrar estadísticas finales
     */
    private function mostrarEstadisticas()
    {
        $this->newLine();
        $this->info('📊 ESTADÍSTICAS ACTUALES:');
        
        $estadisticas = [
            'Normal' => Producto::where('estado', 'Normal')->count(),
            'Bajo stock' => Producto::where('estado', 'Bajo stock')->count(),
            'Por vencer' => Producto::where('estado', 'Por vencer')->count(),
            'Vencido' => Producto::where('estado', 'Vencido')->count(),
            'Agotado' => Producto::where('estado', 'Agotado')->count(),
        ];
        
        foreach ($estadisticas as $estado => $cantidad) {
            $icono = $this->getIconoEstado($estado);
            $this->info("   {$icono} {$estado}: {$cantidad} productos");
        }
    }
    
    /**
     * Obtener icono para cada estado
     */
    private function getIconoEstado(string $estado): string
    {
        return match($estado) {
            'Normal' => '✅',
            'Bajo stock' => '⚠️',
            'Por vencer' => '🟡',
            'Vencido' => '🔴',
            'Agotado' => '⚫',
            default => '📦'
        };
    }
}