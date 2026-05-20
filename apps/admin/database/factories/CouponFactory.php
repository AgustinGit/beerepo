<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CouponApplicableTo;
use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(Str::random(8)),
            'type' => CouponType::Percent,
            'value' => 10,
            'max_uses' => 100,
            'used_count' => 0,
            'min_order_total' => null,
            'applicable_to' => CouponApplicableTo::All,
            'starts_at' => null,
            'expires_at' => now()->addMonth(),
            'active' => true,
        ];
    }

    public function fixed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CouponType::Fixed,
            'value' => 100,
        ]);
    }
}
