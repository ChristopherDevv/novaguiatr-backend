<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount'
    ];

    //el carrito le pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //un carrito puede tener muchas guitarras
    public function guitars()
    {
        //hacemos la relacion e indicamos la tabla intermedia
        return $this->belongsToMany(Guitar::class,"cart_items");
    }

    //un carrito puede tener muchos elementos de carrito
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

}
