<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Autenticación (guard "customer", separado del admin User).
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->rememberToken();

            $table->string('document_id')->nullable();
            // FK a addresses se omite a propósito: addresses se crea después
            // y la relación es circular. Se valida a nivel aplicación.
            $table->unsignedBigInteger('default_address_id')->nullable();
            $table->text('notes_admin')->nullable();
            $table->boolean('marketing_opt_in')->default(false);

            // Denormalizado para performance.
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->integer('loyalty_points')->default(0);
            $table->decimal('wallet_balance', 12, 2)->default(0);

            $table->string('source')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
