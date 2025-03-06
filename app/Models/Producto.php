<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','descripcion','precio','stock','modelo','estado','marca_id','categoria_id'];

    // relaciones pertenece "a" o la inversa de la relacion
    public function marca(){
       return $this->belongsTo(Marca::class);
    }
    public function categoria(){
        return $this->belongsTo(Categoria::class);

    }
    // relacion de uno a muchos con imagenes
    public function imagenes(){
        return $this->hasMany(Imagen::class);
    }
    //relacion de uno a muchos con Detalle_Ordenes
    public function detalleOrdenes(){
        return $this->hasMany(DetalleOrden::class);
    }

}
