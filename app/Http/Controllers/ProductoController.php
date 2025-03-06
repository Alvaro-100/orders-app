<?php

namespace App\Http\Controllers;
use App\Models\Imagen;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            return response()->json(Producto::with('marca','categoria','imagenes')->get());
         }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage(),500]);
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
            /*hacemos una consulta para verificar que no exista un producto con el 
            mismo nombre, descripcion, marca y categoria
            */
            //decodificamos el request
            $productoRequest = json_decode($request->input("producto"),true);
               
            $record = Producto::where("nombre", $productoRequest["nombre"] ?? null)
            ->where("descripcion", $productoRequest["descripcion"] ?? null)
            ->whereHas('marca', function($query) use ($productoRequest) {
                if (isset($productoRequest["marca"]["nombre"])) {
                    $query->where("nombre", $productoRequest["marca"]["nombre"]);
                }
            })
            ->whereHas('categoria', function($query) use ($productoRequest) {
                if (isset($productoRequest["categoria"]["nombre"])) {
                    $query->where("nombre", $productoRequest["categoria"]["nombre"]);
                }
            })
            ->first();
            if($record){
                return response()->json(
                    ["message"=>"Ya existe un registro de producto con estos datos"],409);
            } 
            //creamos una instancia de producto
            $producto = new Producto();
            $producto->nombre = $productoRequest["nombre"];
            $producto->descripcion = $productoRequest["descripcion"];
            $producto->precio = $productoRequest["precio"];
            $producto->stock = $productoRequest["stock"];
            $producto->modelo = $productoRequest["modelo"];
            $producto->estado = $productoRequest["estado"];
            $producto->marca_id = $productoRequest["marca"]["id"];
            $producto->categoria_id = $productoRequest["categoria"]["id"];
            $producto->save(); //guardamos en la tabla de productos
            //verificamos si la peticion trae imagenes
            if($request->hasFile('imagenes')){
                //recorremos la coleccion de imagenes
                foreach($request->file('imagenes') as $img){
                    //generamos un nombre único de la imagen a partir del original
                    $imageName = time() . '_' . $img->getClientOriginalName();
                    //subimos la imagen a la carpeta publica del servidor
                    $img->move(public_path('images/products/'),$imageName);
                    //creamos la instancia de Imagen para guardar los registros
                    $image = new Imagen();
                    $image->nombre = $imageName;
                    $image->producto_id = $producto->id;
                    $image->save(); 
                }
            }
            $prodPersisted = $this->show($producto->id);
            return response()->json(["producto"=>$prodPersisted,
                "message"=>"Producto registrado con éxito...!"],201);
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
            return response()->json(Producto::with('marca','categoria','imagenes')->findOrFail($id));
         }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage(),500]);
         }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    try {
        $producto = Producto::findOrFail($id);
        $producto->nombre = $request->nombre;
        $producto->descripcion = $request->descripcion;
        $producto->precio = $request->precio;
        $producto->stock = $request->stock;
        $producto->modelo = $request->modelo;
        $producto->estado = $request->estado;
        $producto->marca_id = $request->marca['id'];
        $producto->categoria_id = $request->categoria['id'];
        $producto->save();

        // Eliminar imágenes antiguas si hay nuevas imágenes en la solicitud
        if ($request->hasFile('imagenes')) {
            // Eliminar las imágenes antiguas
            foreach ($producto->imagenes as $img) {
                $image_path = public_path() . '/images/products/' . $img->nombre;
                unlink($image_path);
                $img->delete();
            }

            // Guardar las nuevas imágenes
            foreach ($request->file('imagenes') as $img) {
                $imageName = time() . '_' . $img->getClientOriginalName();
                $img->move(public_path('images/products/'), $imageName);

                $image = new Imagen();
                $image->nombre = $imageName;
                $image->producto_id = $producto->id;
                $image->save();
            }
        }
        $prodPersisted = $this->show($producto->id);
        return response()->json(["producto" => $prodPersisted, "message" => "Producto actualizado con éxito...!"], 202);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $producto = Producto::findOrFail($id);
            // eliminamos las imágenes del servidor
            foreach($producto->imagenes as $image) {
                $imagePath = public_path() . '/images/products/' . $image->nombre;
                unlink($imagePath);
            }
        
            // eliminamos los registros de la tabla de imágenes
            $producto->imagenes()->delete();
            if($producto->delete() > 0) {
                return response()->json(["message" => "Producto eliminado"], 205);
            } else {
                return response()->json(["message" => "Ocurrió un error al eliminar el producto"], 409);
            }
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }
}
