<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Nullable: el checkout invitado no requiere cuenta.
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->json('shipping_address_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->string('coupon_code')->nullable();
            $table->string('source')->default('web');
            $table->string('mp_payment_id')->nullable()->index();
            $table->string('mp_payment_status')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            // delivery_id se vincula en Fase 2 (dominio Logística). Sin FK aún.
            $table->unsignedBigInteger('delivery_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
