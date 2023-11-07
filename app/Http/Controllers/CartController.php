<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Guitar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{

    //obtener los articulos del carrito del usuario
    /* En este código, primero obtenemos el carrito del usuario por su ID utilizando el método where() en el modelo 
    Cart. Si se encuentra un carrito para el usuario especificado, obtenemos todos los elementos del carrito 
    asociados con ese carrito utilizando la relación cartItems definida en el modelo Cart. Luego, recorremos 
    cada elemento del carrito y agregamos información sobre ese elemento a una matriz de elementos. Finalmente, 
    devolvemos una respuesta en formato JSON que contiene información sobre los elementos en el carrito del usuario 
    y el total_amount del carrito. */
    public function getCartItems($id)
    {
        //obtener el carrito del usuario por su id (id del usuario)
        $cart = Cart::where('user_id', $id)->first();

        if(isset($cart)){
            //obtener todos los elemnetos del carrito asociados a este carrito
            $cart_items = $cart->cartItems;

            //crear un array para almacenar la informacion de los elementos del carrito (info guitarra)
            //la variable $items es un array que contiene información sobre cada elemento del carrito, incluyendo 
            //la guitarra asociada, la cantidad de esa guitarra en el carrito y el subtotal para ese elemento.
            $items = [];

            //recorrer cada elemento del carrito (cada item asociado a una guitarra)
            foreach ($cart_items as $cart_item) {
                //agregar infortmacion sobre este elemento al array de elemntos
                $items[] = [
                    'guitar' => $cart_item->guitar,
                    'quantity' => $cart_item->quantity,
                    'subtotal' => $cart_item->subtotal,
                ];
            }

            return response()->json([
                'items' => $items,
                'total_amount' => $cart->total_amount,
            ]);

        }else {
            return response()->json([
                'error' => 'User cart not found'
            ]);
        }
    }

    //metodo para añadir elementos al cartito
    public function addToCart(Request $request)
    {
        try {
            /* Al agregar la regla de validación numeric, le estás indicando a Laravel que trate el campo quantity 
            como un campo numérico y aplique las reglas de validación min y max en consecuencia. De esta manera, si 
            envías una cantidad igual a 0, Laravel debería generar una respuesta en formato JSON con un mensaje de 
            error indicando que el campo quantity debe tener un valor mínimo de 1. */
            $request->validate([
                'guitar_id' => 'required',
                'quantity' => 'required|numeric|min:1|max:3'
            ]);
            
            //obtenemos los datos del formulario enviado
            $guitar_id = $request->guitar_id;
            $quantity = $request->quantity;

            // Buscar la guitarra en la base de datos
            $guitar = Guitar::find($guitar_id);

            if(isset($guitar)){
                //varificamos si hay suficiente stock
                if($guitar->stock >= $quantity){
                    //obtener el carrito del usuario
                    /*Lo que hace es buscar el carrito de compras asociado al usuario que está haciendo la solicitud. 
                    El método where del modelo Cart se usa para agregar una cláusula where a la consulta, especificando 
                    que se quiere buscar un registro en la tabla carts cuyo campo user_id sea igual al id del usuario 
                    autenticado. El método first se usa para obtener el primer registro que cumpla con esta condición, 
                    es decir, el carrito del usuario. El método Auth::id() se usa para obtener el id del usuario autenticado.  */
                    $cart = Cart::where('user_id', Auth::id())->first();

                    //calcular el subtotal
                    $subtotal = $quantity * $guitar->actual_price;

                     // Verificar si ya existe un elemento del carrito con esta guitarra
                    $cart_item = CartItem::where('cart_id', $cart->id)
                        ->where('guitar_id', $guitar->id)
                        ->first();
                    
                    if($cart_item){
                        // Ya existe un elemento del carrito con esta guitarra
                        // Actualizar la cantidad y el subtotal de ese elemento
                        $cart_item->quantity += $quantity;
                        $cart_item->subtotal += $subtotal;
                        $cart_item->save();

                        // Actualizar el total_amount del carrito
                        $cart->total_amount += $subtotal;
                        $cart->save();
                    }else{
                        // No existe un elemento del carrito con esta guitarra
                        //agregamos el elemento al carrito
                        CartItem::create([
                            'cart_id' => $cart->id,
                            'guitar_id' => $guitar->id,
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                        ]);

                        //actualizar el monto total del carrito
                        $cart->total_amount += $subtotal;
                        $cart->save();
                    }

                    // Actualizar el stock de la guitarra
                    $guitar->stock -= $quantity;
                    $guitar->save();

                    //respuesta de exito
                    return response()->json([
                        'messge' => 'Item added to cart successfully'
                    ]);
                    
            }else {
                // No hay suficiente stock
                // Devolver una respuesta en formato JSON con un mensaje de error
                return response()->json([
                    'message' => 'Not enough stock'
                ]);
            }

        }else{
            return response()->json([
                'error' => 'guitar not found'
            ]);
        }

        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }

        
    }

    //metodo para actualizar carrito (id del CartItem)
    public function updateCartItem(Request $request, $id)
    {
        try {
            $request->validate([
                'new_quantity' => 'required|numeric|min:1|max:3'
            ]);

            //obtenermos los datos enviados por le form
            $cart_item_id = $id;
            $new_quantity = $request->new_quantity;

            //buscar el item de la guitarra en la db
            $cart_item = CartItem::find($cart_item_id);

           if(isset($cart_item))
           {
                // Calcular la diferencia entre la nueva cantidad y la cantidad anterior
                /* supongamos que un usuario tiene un elemento en su carrito con una guitarra y una cantidad de 2. Si el usuario 
                desea actualizar la cantidad a 3, entonces $new_quantity sería 3 y $cart_item->quantity sería 2. Al restar 2 de 3, 
                se obtiene una diferencia de 1, lo que indica que se está agregando 1 guitarra adicional al elemento del carrito.
                Otro ejemplo podría ser si el usuario desea reducir la cantidad a 1. En este caso, $new_quantity sería 1 y 
                $cart_item->quantity seguiría siendo 2. Al restar 2 de 1, se obtiene una diferencia de -1, lo que indica que se 
                está eliminando 1 guitarra del elemento del carrito. */
                $quantity_difference = $new_quantity - $cart_item->quantity;

                /* supongamos que un usuario tiene un elemento en su carrito con una guitarra y una cantidad de 2. Si el usuario desea 
                actualizar la cantidad a 3, entonces $quantity_difference sería 1 y el código 
                if($cart_item->guitar->stock >= $quantity_difference){} verificaría si hay al menos 1 guitarra en stock. Si hay suficiente 
                stock, el código dentro del bloque if se ejecutará y actualizará la cantidad y el subtotal del elemento del carrito, 
                así como el total_amount del carrito y el stock de la guitarra.

                Otro ejemplo podría ser si el usuario desea reducir la cantidad a 1. En este caso, $quantity_difference sería -1 y el 
                código if($cart_item->guitar->stock >= $quantity_difference){} siempre se evaluará como verdadero, ya que cualquier número 
                es mayor o igual que -1. Esto significa que el código dentro del bloque if siempre se ejecutará en este caso, 
                independientemente del stock actual de la guitarra. */
                if($cart_item->guitar->stock >= $quantity_difference){
                    
                //calcular el nuevo subtotal del cariito
                $new_subtotal = $new_quantity * $cart_item->guitar->actual_price;
    
                //calculamos el total_amount del carrito
                /* Ese código está actualizando el total_amount del carrito de compras cuando se actualiza la cantidad de un elemento 
                en el carrito. Primero, se resta el subtotal anterior del elemento del total_amount del carrito, luego se suma el 
                nuevo subtotal del elemento al total_amount del carrito y finalmente se guarda el cambio en la base de datos */
                /* Por ejemplo, supongamos que un usuario tiene un elemento en su carrito con una guitarra que cuesta $100 y una cantidad de 2, 
                lo que da un subtotal de $200 para ese elemento. Si el usuario desea actualizar la cantidad a 3, entonces el nuevo subtotal 
                sería $300. El código $cart_item->cart->total_amount -= $cart_item->subtotal; restaría $200 del total_amount actual del carrito 
                para eliminar el subtotal anterior del elemento. Luego, el código $cart_item->cart->total_amount += $new_subtotal; sumaría $300 
                al total_amount actual del carrito para agregar el nuevo subtotal del elemento. */
                $cart_item->cart->total_amount -= $cart_item->subtotal;
                $cart_item->cart->total_amount += $new_subtotal;
                $cart_item->cart->save();
    
                // Actualizar el stock de la guitarra
                /* Restar un número negativo es lo mismo que sumar su valor absoluto debido a las propiedades de los números negativos y 
                la resta. El valor absoluto de un número es su distancia desde cero en la recta numérica, sin tener en cuenta su signo. 
                Por ejemplo, el valor absoluto de -3 es 3, ya que -3 está a una distancia de 3 unidades desde cero en la recta numérica. */
                $cart_item->guitar->stock -= $quantity_difference;
                $cart_item->guitar->save();
    
                // Actualizar la cantidad y el subtotal del elemento
                $cart_item->quantity = $new_quantity;
                $cart_item->subtotal = $new_subtotal;
                $cart_item->save();
                
    
                // Devolver una respuesta en formato JSON
                return response()->json([
                    'message' => 'Cart item updated successfully'
                ]);
            }else{
                // Devolver una respuesta en formato JSON
                return response()->json([
                    'message' => 'insufficient guitar stock'
                ]);
            }
             
           }else{
              // Devolver una respuesta en formato JSON
              return response()->json([
                'message' => 'item not found'
            ]);
           }    

        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }

    }

    //eliminamos un elemento del carrito (id dek cartItem)
    public function destroyCartItem($id)
    {
        $cart_item = CartItem::find($id);
        if(isset($cart_item)){

            //actualizamos el stock de guitarra
            $cart_item->guitar->stock += $cart_item->quantity;
            $cart_item->guitar->save();

            //el total del carrito se actualize
            $cart_item->cart->total_amount -= $cart_item->subtotal;
            $cart_item->cart->save();

            //eliminamos el item
            CartItem::destroy($id);

            return response()->json([
                'message' => 'Cart item deleted successfully'
            ]);

        }else{
            return response()->json([
                'error' => 'Cart item not found'
            ]);
        }
    }

    //vaciamos el carrito(id del carito)
    public function emptyCart($id)
    {
        //obtenermos le carrito por su id
        $cart = Cart::find($id);
        if(isset($cart)){
            //obtenemos todos los elementos del carrito asociados con ese carrito
            $cart_items = $cart->cartItems;

            //recorremos cada elemnto del carrito
            foreach ($cart_items as $cart_item) {
               //En este código, $guitar es una variable temporal que almacena la guitarra actual del elemento del 
               //carrito. Luego modificamos el stock de $guitar y guardamos $guitar
              //actualizamos el stock de la guitarra
              if ($cart_item->guitar) {
                $guitar = $cart_item->guitar;
                $guitar->stock += $cart_item->quantity;
                $guitar->save();
            }
            }

            //eliminamos todos los elemntos del carrito asociados con ese carrito
            $cart->cartItems()->delete();

            //actualizamos el total amount del carrito
            $cart->total_amount = 0;
            $cart->save();

            return response()->json([
                'message' => 'Cart emptied successfully'
            ]);

        }else{
            return response()->json([
                'error' => 'Cart not found'
            ]);
        }
    }
    
}
