<?php

namespace App\Http\Controllers;
use App\Models\DetalleOrden;
use App\Models\Orden;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Nette\Schema\ValidationException as SchemaValidationException;
use PhpParser\Node\Expr\Cast\Array_;

class OrdenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            return response()->json(Orden::with('user','detalleOrdenes.producto')->get());
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
            //validando datos del $request
            $validData = $request->validate([
                'fecha' =>'required|date',
                'estado' =>'required|string',
                'total' =>'required|numeric|min:0',
                'user.id' =>'required|exists:users,id',
                'detalleOrdenes' => 'required|array|min:1',
                'detalleOrdenes.*.cantidad' => 'required|numeric|min:1',
                 'detalleOrdenes.*.precio' => 'required|numeric',
                 'detalleOrdenes.*.producto.id' => 'required|exists:productos,id'
            ]);
            //iniciamos la transaccion
            DB::beginTransaction();
            // guardando el registro en Orden (tabla ordenes)
 
            $orden = Orden::create([
                'correlativo'=>$this->getCorrelativo(),
                'fecha' => $validData['fecha'],
                'estado' => $validData['estado'],
                'total' => $validData['total'],
                'user_id' => $validData['user'] ['id'],
            ]);
            // preparamos el detallepara hacer inserción masiva
 
            $detalleData = collect($validData['detalleOrdenes'])->map(function ($det) use ($orden){
               return [
                  'cantidad'=>$det['cantidad'],
                  'precio'=>$det['precio'],
                  'producto_id'=>$det['producto'] ['id'],
                  'orden_id'=>$orden->id,
                  'Created_at'=>now(),
                  'updated_at'=>now()
               ];
            })->toArray();
            DetalleOrden::insert($detalleData);
            //confirmamos la transaccion 
            DB::commit();
            return response()->json([
                "orden" => $orden->load('detalleOrdenes'),
                "message" => "su orden ha sido registrada correctamente"
            ], 201);
 
         }catch(validationException $e){
             return response()->json(["errors" => $e ->errors(),"message" => "error en la validacion de los datos"],400);
         }catch(\Exception $e){
             return response()->json(['error'=>$e->getMessage(),500]);
         }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
         try{
            return response()->json(Orden::with('user','detalleOrdenes.producto')->findOrFail($id));
       }catch(\Exception $e){
           return response()->json(['error'=>$e->getMessage(),500]);
       }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Orden $orden)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try{
            $orden = Orden::find($id);
            if(!$orden){
                return response()->json([
                    'status'=>'Not Found',
                    'message'=>'orden no encontrada'
                    ],404);
            }
            //definimos estados de la orden 
            $estados = ['R'=>'Recibida','D'=>'Despachada','A','Anulada'];
            //verificamos el estado enviado
            if(!Array_key_exists($request->estado,$estados)){
                return response()->json([
                    'status'=>'Bad request',
                    'message'=>'Estado invalido'
                    ],404);
            }
            //obtenemos la fecha del servidor
            $fechaActual = Carbon::now()->toDateString();
            if($request->estado ==='D'){
                $orden->fecha_despacho = $fechaActual;
            }
            //acutalizamos el estado de la orden 
            $orden->estado = $request->estado;
            $orden->save();
            return response()->json([
                'status'=>'Accepted',
                'message'=>'El estado de la orden No' .$orden->correlativo .'ha sido cambiado a:'.$estados[$orden->estados]
                ],404);
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Orden $orden)
    {
        //
    }
    public function getCorrelativo(){
        $result = DB::select("SELECT CONCAT(TRIM(YEAR(CURDATE())),LPAD(TRIM(MONTH(CURDATE())),2,0),LPAD(IFNULL(MAX(TRIM(SUBSTRING(correlativo,7,4))),0)+1,4,0)) as correlativo FROM ordenes WHERE SUBSTRING(correlativo,1,6)=CONCAT(TRIM(YEAR(CURDATE())),LPAD(TRIM(MONTH(CURDATE())),2,0))");
        return $result[0]->correlativo;
    }
}
