<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'plan_id' => Plan::factory(),
            'status' => SubscriptionStatus::Active,
            'mp_subscription_id' => fake()->uuid(),
            'start_date' => now(),
            'next_billing_date' => now()->addMonth(),
            'total_paid_cycles' => 0,
            'preferences' => null,
        ];
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SubscriptionStatus::Paused,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
