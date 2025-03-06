<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CategoriasController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\ProductoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::apiResource('marcas',MarcaController::class);
Route::apiResource('productos',ProductoController::class);
Route::apiResource('categorias', CategoriaController::class);
Route::apiResource('ordenes',OrdenController::class);