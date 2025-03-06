<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
           return response()->json(Marca::all());
        }catch(\Exception $e){
           return response()->json(['error'=>$e->getMessage()],500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            // primero verificamos que el nombre de marca no exista
            $existeMarca = Marca::where("nombre", $request->nombre)->first();
            if($existeMarca){
              return response()->json(["message"=>"esta Marca ya esta registrada en la base de datos"],409);
            }else{    
                $marca = Marca::create($request->all());
                return response()->json(["marca"=>$marca,"message"=>"Marca registrado con exito...!"],201);
            }
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()],500);

        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
           return response()->json(Marca::findOrFail($id));
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Marca $marca)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        try{
            $existeMarca = Marca::where("nombre", $request->nombre)->first();
            if($existeMarca && $existeMarca->id != $id){
                return response()->json(["message"=>"Ya existe otro registro con esta marca...."],409);
            }else{
               $marca = Marca::findOrFail($id);
               $marca->nombre = $request->nombre;
               if($marca->update() > 0)
                   return response()->json(["marca"=>$marca,"message"=>"marca actualizada...!"],202);
               else
               return response()->json(["message"=>"Ocurrio un error al actualizar el registro, intente de nuevo"],500);
            } 
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
            $marca = Marca::findOrFail($id);
            if($marca->delete() > 0){
                return response()->json(["message"=>"marca eliminado...."],200);
            }else{
               return response()->json(["message"=>"Imposible eliminar el registro..!"],500);
            } 
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()],500);
        }
    }
}
