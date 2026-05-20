<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('billing_cycle');
            $table->date('scheduled_send_date')->nullable();
            $table->date('sent_date')->nullable();
            $table->json('contents')->nullable();
            $table->boolean('includes_glass')->default(false);
            $table->json('shipping_address_snapshot')->nullable();
            // delivery_id se vincula en Fase 2 (dominio Logística). Sin FK aún.
            $table->unsignedBigInteger('delivery_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_boxes');
    }
};
