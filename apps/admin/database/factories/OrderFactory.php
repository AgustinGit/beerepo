<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 360, 2000);
        $shipping = fake()->randomElement([0, 150]);

        return [
            'customer_id' => Customer::factory(),
            'status' => OrderStatus::Pending,
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'discount_total' => 0,
            'total' => $subtotal + $shipping,
            'shipping_address_snapshot' => null,
            'notes' => null,
            'coupon_code' => null,
            'source' => OrderSource::Web,
        ];
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes): array => [
            'customer_id' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatus::Paid,
            'mp_payment_id' => (string) fake()->randomNumber(8, true),
            'mp_payment_status' => 'approved',
            'paid_at' => now(),
        ]);
    }
}
