<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity');
            $table->decimal('subtotal',8,2);

            /* si se elimina una guitarra de la tabla guitars, entonces todos los registros en la tabla cart_items
             que estén relacionados con esa guitarra a través de la clave foránea guitar_id también se eliminarán,
              debido a que se especificó onDelete('cascade') al definir la relación entre las tablas guitars y 
              cart_items. Esto significa que si un usuario tenía esa guitarra en su carrito, el elemento 
              correspondiente en la tabla cart_items se eliminará automáticamente cuando se elimine la guitarra de 
              la tabla guitars. */
             /*  cuando se elimina un carrito de compras, todos los elementos del carrito asociados con ese carrito también se eliminarán automáticamente. */
            //relacion con cart
            $table->unsignedBigInteger('cart_id');
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');

            //relacion con guitarra
            $table->unsignedBigInteger('guitar_id');
            $table->foreign('guitar_id')->references('id')->on('guitars')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
