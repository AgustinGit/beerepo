<?php

declare(strict_types=1);

use App\Enums\CustomerSource;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Subscription;

it('creates a customer', function () {
    $customer = Customer::factory()->create([
        'email' => 'socio@example.com',
        'source' => CustomerSource::Referido,
    ]);

    expect($customer->exists)->toBeTrue()
        ->and($customer->source)->toBe(CustomerSource::Referido)
        ->and($customer->wallet_balance)->toBe('0.00');

    $this->assertDatabaseHas('customers', ['email' => 'socio@example.com']);
});

it('has addresses, subscriptions and orders', function () {
    $customer = Customer::factory()
        ->has(Address::factory()->count(2))
        ->has(Subscription::factory())
        ->has(Order::factory()->count(3))
        ->create();

    expect($customer->addresses)->toHaveCount(2)
        ->and($customer->subscriptions)->toHaveCount(1)
        ->and($customer->orders)->toHaveCount(3);
});

it('hides the password when serialized', function () {
    $customer = Customer::factory()->create();

    expect($customer->toArray())->not->toHaveKey('password');
});
