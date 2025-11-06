<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre', 'descripcion', 'estado'
    ];

    protected $casts = [
        'estado' => 'string',
    ];

    // Incluir 'activo' en conversiones a array/JSON para la UI
    protected $appends = ['activo'];

    // Scope para obtener solo categorías activas
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activo');
    }

    public function productos()
    {
        // Relación por nombre de categoría (productos.categoria -> categorias.nombre)
        return $this->hasMany(Producto::class, 'categoria', 'nombre');
    }

    // Compatibilidad: prop "activo" derivada del campo "estado" (string)
    public function getActivoAttribute()
    {
        return ($this->estado === 'activo');
    }
}