<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guitar extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'full_name',
        'category',
        'color',
        'description',
        'specifications',
        'care_maintenance',
        'rating',
        'price',
        'discount',
        'actual_price',
        'stock',
        'image_url'
    ];

   

     //un carrito puede tener muchas guitarras
    public function carts()
    {
        //hacemos la relacion e indicamos la tabla intermedia
        return $this->belongsToMany(Cart::class,"cart_items");
    }

     /* un método en tu modelo Guitar que se ejecute cuando se elimine una guitarra. Este método puede calcular el nuevo total_amount 
    para cada carrito afectado por la eliminación de la guitarra y actualizar los registros en la tabla carts en consecuencia. */

    /* En Laravel, el método booted es un método estático en un modelo de Eloquent que se llama después de que el 
    modelo se haya “arrancado” o cargado por el framework de Laravel. Este método proporciona un lugar conveniente 
    para registrar escuchas de eventos o realizar otras tareas de configuración.
    En el código que mencionas, static::deleting(function ($guitar) {...} registra un evento deleting en el modelo 
    Guitar que se ejecutará cuando se elimine una guitarra de la base de datos. El evento se registra utilizando 
    el método booted() del modelo, que es llamado automáticamente por Laravel cuando el modelo se inicializa.
    Dentro del evento deleting, el código recibe como parámetro una instancia del modelo Guitar que representa 
    la guitarra que se está eliminando. Esta instancia se pasa automáticamente al evento por Laravel cuando 
    se dispara el evento deleting. El código dentro del evento puede utilizar esta instancia para realizar 
    acciones adicionales antes de que la guitarra sea eliminada, como actualizar otros registros en la base 
    de datos o realizar otras tareas. */
    protected static function booted()
    {
        static::deleting(function ($guitar) {
            // Obtener todos los elementos del carrito asociados con esta guitarra
            $cart_items = $guitar->cartItems;

            // Recorrer cada elemento del carrito
            foreach ($cart_items as $cart_item) {
                // Restar el subtotal del elemento del total_amount del carrito
                $cart_item->cart->total_amount -= $cart_item->subtotal;
                $cart_item->cart->save();
            }
        });
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    
}
