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
        Schema::create('guitars', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('full_name');
            $table->string('category');
            $table->string('color');
            $table->text('description');
            $table->text('specifications');
            $table->text('care_maintenance');
            $table->integer('rating');
            $table->decimal('price',8,2);
            $table->integer('discount');
            $table->decimal('actual_price',8,2);
            $table->integer('stock');
            $table->longText('image_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guitars');
    }
};
