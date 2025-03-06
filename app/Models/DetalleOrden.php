<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleOrden extends Model
{
    use HasFactory;
    protected $table = "detalle_ordenes";
    protected $fillable = ['cantidad','precio','producto_id','orden_id'];

    // relacion con el modelo producto
    public function producto(){
        return $this->belongsTo(Producto::class);
    }
    
    public function orden(){
        return $this->belongsTo(Orden::class);
    }
}
