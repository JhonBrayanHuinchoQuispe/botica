<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre', 'descripcion', 'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function productos()
    {
        // Relación por nombre de categoría (productos.categoria -> categorias.nombre)
        return $this->hasMany(Producto::class, 'categoria', 'nombre');
    }
}