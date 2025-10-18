<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use App\Models\Producto;
use App\Models\User;

class ProductLot extends Model
{
    use HasFactory;

    protected $table = 'product_lots';

    protected $fillable = [
        'producto_id',
        'numero_lote',
        'fecha_fabricacion',
        'fecha_vencimiento',
        'fecha_entrada',
        'cantidad_inicial',
        'cantidad_actual',
        'precio_compra',
        'precio_venta',
        'registro_sanitario',
        'fabricante',
        'pais_origen',
        'temperatura_almacenamiento',
        'estado',
        'observaciones',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'fecha_fabricacion' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_entrada' => 'date',
        'cantidad_inicial' => 'integer',
        'cantidad_actual' => 'integer',
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
    ];

    protected $appends = [
        'dias_hasta_vencimiento',
        'porcentaje_stock',
        'esta_vencido',
        'esta_por_vencer',
        'esta_agotado',
        'estado_texto',
        'estado_color'
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getDiasHastaVencimientoAttribute()
    {
        return Carbon::now()->diffInDays($this->fecha_vencimiento, false);
    }

    public function getPorcentajeStockAttribute()
    {
        if ($this->cantidad_inicial == 0) return 0;
        return round(($this->cantidad_actual / $this->cantidad_inicial) * 100, 2);
    }

    public function getEstaVencidoAttribute()
    {
        return Carbon::now()->gt($this->fecha_vencimiento);
    }

    public function getEstaPorVencerAttribute()
    {
        $diasHastaVencimiento = $this->dias_hasta_vencimiento;
        return $diasHastaVencimiento > 0 && $diasHastaVencimiento <= 30;
    }

    public function getEstaAgotadoAttribute()
    {
        return $this->cantidad_actual <= 0;
    }

    public function getEstadoTextoAttribute()
    {
        if ($this->esta_vencido) return 'Vencido';
        if ($this->esta_agotado) return 'Agotado';
        if ($this->esta_por_vencer) return 'Por vencer';
        if ($this->estado === 'retirado') return 'Retirado';
        return 'Activo';
    }

    public function getEstadoColorAttribute()
    {
        if ($this->esta_vencido) return 'danger';
        if ($this->esta_agotado) return 'secondary';
        if ($this->esta_por_vencer) return 'warning';
        if ($this->estado === 'retirado') return 'dark';
        return 'success';
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo')
                    ->where('cantidad_actual', '>', 0)
                    ->where('fecha_vencimiento', '>', now());
    }

    public function scopeVencidos($query)
    {
        return $query->where('fecha_vencimiento', '<=', now());
    }

    public function scopePorVencer($query, $dias = 30)
    {
        return $query->where('fecha_vencimiento', '>', now())
                    ->where('fecha_vencimiento', '<=', now()->addDays($dias));
    }

    public function scopeAgotados($query)
    {
        return $query->where('cantidad_actual', '<=', 0);
    }

    public function scopeOrdenadosPorFIFO($query)
    {
        return $query->orderBy('fecha_vencimiento', 'asc')
                    ->orderBy('fecha_entrada', 'asc');
    }

    // ==========================================
    // MÉTODOS DE NEGOCIO
    // ==========================================

    /**
     * Reduce el stock del lote
     */
    public function reducirStock($cantidad)
    {
        if ($cantidad > $this->cantidad_actual) {
            throw new \Exception("No hay suficiente stock en el lote {$this->numero_lote}");
        }

        $this->cantidad_actual -= $cantidad;
        
        if ($this->cantidad_actual <= 0) {
            $this->estado = 'agotado';
        }

        $this->save();
        return $this;
    }

    /**
     * Aumenta el stock del lote
     */
    public function aumentarStock($cantidad)
    {
        $this->cantidad_actual += $cantidad;
        
        if ($this->estado === 'agotado' && $this->cantidad_actual > 0) {
            $this->estado = 'activo';
        }

        $this->save();
        return $this;
    }

    /**
     * Marca el lote como vencido si corresponde
     */
    public function verificarVencimiento()
    {
        if ($this->esta_vencido && $this->estado === 'activo') {
            $this->estado = 'vencido';
            $this->save();
        }
        return $this;
    }

    /**
     * Retira el lote del inventario
     */
    public function retirar($observacion = null)
    {
        $this->estado = 'retirado';
        if ($observacion) {
            $this->observaciones = $observacion;
        }
        $this->save();
        return $this;
    }

    /**
     * Valida si el lote puede ser usado para venta
     */
    public function puedeVenderse()
    {
        return $this->estado === 'activo' 
               && $this->cantidad_actual > 0 
               && !$this->esta_vencido;
    }

    // ==========================================
    // MÉTODOS ESTÁTICOS
    // ==========================================

    /**
     * Obtiene el próximo lote disponible para un producto (FIFO)
     */
    public static function proximoLoteDisponible($productoId, $cantidadRequerida = 1)
    {
        return static::where('producto_id', $productoId)
                    ->activos()
                    ->where('cantidad_actual', '>=', $cantidadRequerida)
                    ->ordenadosPorFIFO()
                    ->first();
    }

    /**
     * Obtiene todos los lotes disponibles para un producto ordenados por FIFO
     */
    public static function lotesDisponibles($productoId)
    {
        return static::where('producto_id', $productoId)
                    ->activos()
                    ->ordenadosPorFIFO()
                    ->get();
    }

    /**
     * Calcula el stock total disponible para un producto
     */
    public static function stockTotalProducto($productoId)
    {
        return static::where('producto_id', $productoId)
                    ->activos()
                    ->sum('cantidad_actual');
    }
}
