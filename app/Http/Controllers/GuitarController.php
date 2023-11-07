<?php

namespace App\Http\Controllers;

use App\Models\Guitar;
use Illuminate\Http\Request;

class GuitarController extends Controller
{
    public function index()
    {
        $guitars = Guitar::all();
        /* La variable $users es una instancia de la clase Illuminate\Database\Eloquent\Collection, que representa 
        una colección de objetos del modelo User. Cada objeto en la colección representa un usuario en la base de datos.
        El método isEmpty devuelve un valor booleano, es decir, true o false. Si la colección está vacía, es decir,
        si no contiene ningún elemento, entonces isEmpty devuelve true. De lo contrario, devuelve false */
        if($guitars->isEmpty()){
            return response()->json([
                'data' => 'there are not guitars yet'
            ]);
        }else{
            return response()->json([
                'data' => $guitars
            ]);
        }
    }

    public function show($id)
    {
        $guitar = Guitar::find($id);
        if(isset($guitar)){
            return response()->json([
                'data' => $guitar
            ]);
            
        }else{
            return response()->json([
                'error' => 'guitar not found'
            ]);
        }
    }

    public function store(Request $request)
    {
       try {
            $request->validate([
                'name' => 'required|unique:guitars',
                'full_name' => 'required',
                'category' => 'required',
                'color' => 'required',
                'description' => 'required',
                'specifications' => 'required',
                'care_maintenance' => 'required',
                'rating' => 'required|numeric|min:1|max:5',
                'price' => 'required|numeric|min:1',
                'discount' => 'required|numeric|min:1',
                'stock' => 'required|numeric|min:1',
                'image_url' => 'required'
            ]);

            $price = $request->price;
            $discountGuitar = $request->discount / 100.0;
            $actual_price =  $price - ($price * $discountGuitar);

            $guitar = new Guitar();
            $guitar->name = $request->name;
            $guitar->full_name = $request->full_name;
            $guitar->category = $request->category;
            $guitar->color = $request->color;
            $guitar->description = $request->description;
            $guitar->specifications = $request->specifications;
            $guitar->care_maintenance = $request->care_maintenance;
            $guitar->rating = $request->rating;
            $guitar->price = $request->price;
            $guitar->discount = $request->discount;
            $guitar->actual_price = $actual_price; 
            $guitar->stock = $request->stock;
            $guitar->image_url = $request->image_url;

            $guitar->save();

            return response()->json([
                'message' => 'successfully created guitar',
                'guitar' => $guitar
            ]);
       } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
       }

    }

    public function update(Request $request, $id)
    {
        /* En este caso, 'required|unique:guitars,name,' . $guitar->id le dice a Laravel que el campo name debe ser 
        único en la tabla guitars, excluyendo el registro con el ID $guitar->id. */
        try {
            $guitar = Guitar::find($id);

            $request->validate([
                'name' => 'required|unique:guitars,name,' . $guitar->id,
                'full_name' => 'required',
                'category' => 'required',
                'color' => 'required',
                'description' => 'required|min:5',
                'specifications' => 'required|min:5',
                'care_maintenance' => 'required|min:5',
                'rating' => 'required|numeric|min:1|max:5',
                'price' => 'required|numeric|min:1',
                'discount' => 'required|numeric|min:1',
                'stock' => 'required|numeric|min:1',
                'image_url' => 'required'
            ]);
    
            //creamos el nuevo costo de la guitarra
            $price = $request->price;
            $discountGuitar = $request->discount / 100.0;
            $actual_price =  $price - ($price * $discountGuitar);

            if(isset($guitar)){
                $guitar->update($request->all());
                $guitar->actual_price = $actual_price;
                $guitar->save();
    
                return response()->json([
                    'guitar' => $guitar,
                    'message' => 'successfully update guitar'
                ]);
            }else{
                return response()->json([
                    'error' => 'guitar with id '.$id.' not found'
                ]);
            }
           
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
                'message' => 'invalidate fields'
            ]);
        }

    }

    public function destroy($id)
    {
        try {
            /* si se encuentra una guitarra con el id especificado en la base de datos, entonces la variable 
            $guitar estará definida y no será null, por lo que isset($guitar) devolverá true. Si no se encuentra 
            una guitarra con el id especificado, entonces la variable $guitar será null y isset($guitar) devolverá false. */
            $guitar = Guitar::find($id);
            if(isset($guitar)){
                Guitar::destroy($id);
                return response()->json([
                    'message' => 'successfully deleted guitar',
                    'guitar' => $guitar
                ]);
            }else{
                return response()->json([
                    'error' => 'guitar not found'
                ]);
        }
        } catch (\Throwable $th) {
           return response()->json([
                'error' => $th->getMessage()
           ]);
        }
    }

}
