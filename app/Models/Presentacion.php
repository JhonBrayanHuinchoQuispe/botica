<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presentacion extends Model
{
    use HasFactory;

    protected $table = 'presentaciones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
        'unidad_venta',
        'factor_unidad_base',
        'precio_venta',
        'permite_fraccionamiento',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'factor_unidad_base' => 'integer',
        'precio_venta' => 'decimal:2',
        'permite_fraccionamiento' => 'boolean',
    ];

    public function productos()
    {
        // Relación por nombre de presentación (productos.presentacion -> presentaciones.nombre)
        return $this->hasMany(Producto::class, 'presentacion', 'nombre');
    }
}
