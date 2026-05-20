<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            // recipe_id se vincula en Fase 2 (dominio Producción). Sin FK aún.
            $table->unsignedBigInteger('recipe_id')->nullable();
            $table->text('description')->nullable();
            $table->decimal('retail_price', 10, 2);
            $table->decimal('wholesale_price', 10, 2);
            $table->string('image_url')->nullable();
            $table->json('gallery')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
