<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\SubscriptionBox;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionBox>
 */
class SubscriptionBoxFactory extends Factory
{
    protected $model = SubscriptionBox::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'billing_cycle' => 1,
            'scheduled_send_date' => now()->addWeek(),
            'sent_date' => null,
            'contents' => null,
            'includes_glass' => false,
            'shipping_address_snapshot' => null,
        ];
    }
}
