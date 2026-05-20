<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('annual_discount_pct', 5, 2)->default(0);
            $table->unsignedInteger('cans_per_box')->default(0);
            $table->unsignedInteger('includes_glass_every_n_months')->nullable();
            $table->json('shipping_included_zones')->nullable();
            $table->decimal('discount_pct_on_purchases', 5, 2)->default(0);
            $table->json('benefits')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
