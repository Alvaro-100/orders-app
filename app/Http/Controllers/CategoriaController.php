<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        try{
            return response()->json(Categoria::all());
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
            $existeCategoria = Categoria::where("nombre", $request->nombre)->first();
            if($existeCategoria){
              return response()->json(["message"=>"esta categoria ya esta registrada en la base de datos"],409);
            }else{    
                $categoria = Categoria::create($request->all());
                return response()->json(["marca"=>$categoria,"message"=>"categoria registrado con exito...!"],201);
            }
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()],500);

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Categoria $id)
    {
        try{
            return response()->json(Categoria::findOrFail($id));
         }catch(\Exception $e){
             return response()->json(['error'=>$e->getMessage()], 500);
         }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categoria $categoria)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try{
            $existeCategoria = Categoria::where("nombre", $request->nombre)->first();
            if($existeCategoria && $existeCategoria->id != $id){
                return response()->json(["message"=>"Ya existe otro registro con esta categoria...."],409);
            }else{
               $categoria = Categoria::findOrFail($id);
               $categoria->nombre = $request->nombre;
               if($categoria->update() > 0)
                   return response()->json(["marca"=>$categoria,"message"=>"categoria actualizada...!"],202);
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
    public function destroy(Categoria $id)
    {
        try{
            $categoria = Categoria::findOrFail($id);
            if($categoria->delete() > 0){
                return response()->json(["message"=>"categoria eliminada...."],200);
            }else{
               return response()->json(["message"=>"Imposible eliminar el registro..!"],500);
            } 
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()],500);
        }
    }
}
