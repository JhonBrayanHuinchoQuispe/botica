<?php

namespace App\Models\PuntoVenta;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Producto;

class VentaDetalle extends Model
{
    use HasFactory;

    protected $table = 'venta_detalles';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // Accessors
    public function getTotalAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }

    // Métodos
    public function calcularSubtotal()
    {
        $this->subtotal = $this->cantidad * $this->precio_unitario;
        $this->save();
        
        return $this;
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detalle) {
            // Calcular subtotal automáticamente
            $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
        });

        static::saved(function ($detalle) {
            // Recalcular totales de la venta padre
            $detalle->venta->calcularTotales();
            // Actualizar stock del producto
            $detalle->producto->actualizarStockVenta($detalle->cantidad, 'unidad');
        });

        static::deleted(function ($detalle) {
            // Recalcular totales de la venta padre
            if ($detalle->venta) {
                $detalle->venta->calcularTotales();
            }
            // Revertir stock si es necesario
            $detalle->producto->agregarStock($detalle->cantidad, 'unidad');
        });
    }
}