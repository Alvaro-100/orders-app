<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    use HasFactory;
    protected $table = "ordenes";

    protected $fillable = ['correlativo','fecha','estado','total','user_id'];
    // relacion con el modelo User
    public function user(){
        return $this->belongsTo(User::class);
    }
    // relacion con el modelo DetalleOrden
    public function detalleOrdenes(){
        return $this->hasMany(DetalleOrden::class);
    }
}
