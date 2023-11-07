<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\GuitarController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */

//rutas de autenticacion
Route::post('user/register', [UserController::class, 'register'])->name('api.register');
Route::post('user/login', [UserController::class, 'login'])->name('api.login');

//rutas publicas
Route::get('guitars', [GuitarController::class, 'index'])->name('api.guitar.index');
Route::get('guitar/{id}', [GuitarController::class, 'show'])->name('api.guitar.show');

//rutas protegidas
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('user/logout', [UserController::class, 'logout'])->name('api.logout');
    Route::put('user/update/{id}', [UserController::class, 'userUpdate'])->name('api.user.update');
    Route::delete('user/destroy/{id}',[UserController::class, 'userDestroy'])->name('api.user.destroy');

    //rutas para el manejo del acrrito del usuario
    Route::get('cart/{id}', [CartController::class, 'getCartItems'])->name('api.cart.get');
    Route::post('cart/addtocart', [CartController::class, 'addToCart'])->name('api.cart.add');
    Route::post('cart/update/{id}', [CartController::class, 'updateCartItem'])->name('api.cart.update');
    Route::delete('cart/destroy/{id}', [CartController::class, 'destroyCartItem'])->name('api.cart.destroy');
    Route::post('cart/empty/{id}', [CartController::class, 'emptyCart'])->name('api.cart.empty');
    
    //rutas para administrador
    Route::group(['middleware' => 'is_admin'], function(){

        Route::post('admin/guitars/store', [GuitarController::class, 'store'])->name('api.guitar.store');
        Route::put('admin/guitars/update/{id}', [GuitarController::class, 'update'])->name('api.guitar.update');
        Route::delete('admin/guitars/destroy/{id}', [GuitarController::class, 'destroy'])->name('api.guitar.destroy');
    
    });
});