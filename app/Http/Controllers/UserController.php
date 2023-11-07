<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function register(Request $request)
    {
       try {
         //creamos reglas de validacion
         $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users|email',
            'password' => 'required|min:6'
        ]);

        //instacionamos un usuario para crear uno nuevo
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->is_admin = false;
        $user->password = Hash::make($request->password);

        $user->save();

        //creamos un carrito para el user
        Cart::create([
            'user_id' => $user->id,
            'total_amount' => 0
        ]);

        //acces token (creamos u token para el nuevo usuario este es creado por Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token
        ]);
       } catch (\Throwable $th) {
            return response()->json([
                'message' => 'registration failed, invalid credentials',
                'error' => $th->getMessage()
            ], 409);
       }
    }

    public function userUpdate(Request $request, $id)
    {
        try {
            $user = User::find($id);
            /* unique:users,email,' . $user->id: El correo electrónico debe ser único en la tabla de usuarios, excepto 
            para el correo electrónico del usuario que se está actualizando.
            La parte unique:users,email,' . $user->id es la que se encarga de la comprobación de unicidad. Aquí, users 
            es el nombre de la tabla donde se quiere comprobar la unicidad, email es el nombre del campo que debe ser 
            único, y $user->id es el ID del usuario que se excluye de la comprobación.
            Por lo tanto, esta regla permite que un usuario actualice su perfil sin cambiar su correo electrónico, 
            ya que su propio correo electrónico se excluye de la comprobación de unicidad */
            $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'password' => 'required|min:6'
            ]);
            
            if(isset($user)){
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $user->is_admin = $request->is_admin;
                $user->password = Hash::make($request->password);
                $user->save();
                //$token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'message' => 'successfully update user',
                    'user' => $user
                ]);
            }else{
                return response()->json([
                    'message' => 'User not found'
                ], 500);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'update register failed, invalid credentials',
                'error' => $th->getMessage()
            ], 500);
        }

    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' =>  'required|min:6'
        ]);
        //el metodo atmp devuleve un boolena si todo es coreecto
        /*  el método Auth::attempt verifica las credenciales del usuario en la base de datos para ver si existe un usuario con el 
        correo electrónico y la contraseña proporcionados. Este método toma un arreglo de credenciales como parámetro, donde las 
        claves son los nombres de las columnas en la tabla de usuarios y los valores son los valores proporcionados por el usuario. */
        if(!Auth::attempt($request->only('email', 'password'))){
            return response()->json([
                'message' => "Authentication has failed"
            ]);
        }

        //creamos un token si paso las vaildaciones
        $access_token = auth()->user()->createToken('auth_token')->plainTextToken;
        return response()->json([
            'user'=> auth()->user(),
            'access_token' => $access_token
        ]);

    }

    public function logout()
    {
        try {
           // Auth::logout(); (con sanctum no existe este metodo)
           // Invalida el token de autenticación del usuario
            //$request->user()->currentAccessToken()->delete();
            auth()->user()->currentAccessToken()->delete();
            return response()->json([
                'message' => 'the session has ben closed successfully'
            ]);
        } catch (\Throwable $th) {
            return response([
                'error' => $th->getMessage()
            ], status:500);
        }
        
    }

    public function userDestroy($id)
    {
        try {
            $user = User::find($id);
            if(isset($user)){
                //obtenemos todos los elementos del carrito asociados con ese carrito
                $cart_items = $user->cart->cartItems;
                 //recorremos cada elemento del carrito
                 foreach($cart_items as $cart_item){
                     //actualizamos el stock de la guitarra
                     $guitar = $cart_item->guitar;
                     $guitar->stock += $cart_item->quantity;
                     $guitar->save();
                 }

                $user->cart->delete();
                User::destroy($id);
                
                return response()->json([
                    'message' => 'successfully delete user',
                ]);
            }else{
                return response()->json([
                    'error' => 'user not found'
                ], 404);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()                
            ], 500);
        }
    }
}
