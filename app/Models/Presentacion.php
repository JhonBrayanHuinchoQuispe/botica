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
        'estado'
    ];

    protected $casts = [
        'estado' => 'string'
    ];

    // Incluir 'activo' en conversiones a array/JSON para la UI
    protected $appends = ['activo'];

    // Scope para obtener solo presentaciones activas
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activo');
    }

    public function productos()
    {
        // Relación por nombre de presentación (productos.presentacion -> presentaciones.nombre)
        return $this->hasMany(Producto::class, 'presentacion', 'nombre');
    }

    // Compatibilidad: prop "activo" derivada de "estado" (string)
    public function getActivoAttribute()
    {
        return ($this->estado === 'activo');
    }
}
