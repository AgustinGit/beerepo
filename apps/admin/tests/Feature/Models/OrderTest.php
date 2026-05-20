<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;

it('creates a pending order for a customer', function () {
    $order = Order::factory()->create();

    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->customer)->not->toBeNull();
});

it('supports guest checkout without a customer', function () {
    $order = Order::factory()->guest()->create();

    expect($order->customer_id)->toBeNull();
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'customer_id' => null]);
});

it('marks an order as paid', function () {
    $order = Order::factory()->paid()->create();

    expect($order->status)->toBe(OrderStatus::Paid)
        ->and($order->paid_at)->not->toBeNull();
});

it('has order items', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(3), 'items')
        ->create();

    expect($order->items)->toHaveCount(3);
});
