<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    public $table = "cart_items";

    protected $fillable = [
        'cart_id',
        'guitar_id',
        'quantity',
        'subtotal'
    ];

    /* En este código, belongsTo(Cart::class) y belongsTo(Guitar::class) definen que un CartItem pertenece a un Cart y a una Guitar. 
    Laravel utilizará estas definiciones para cargar automáticamente las instancias de Cart y Guitar asociadas cuando accedas a las 
    propiedades $cart_item->cart y $cart_item->guitar. */

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function guitar()
    {
        return $this->belongsTo(Guitar::class);
    }


    
}
