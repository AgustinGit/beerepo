<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'monthly_price' => fake()->randomFloat(2, 300, 1200),
            'annual_discount_pct' => 0,
            'cans_per_box' => fake()->randomElement([0, 6]),
            'includes_glass_every_n_months' => null,
            'shipping_included_zones' => ['montevideo', 'canelones', 'costa'],
            'discount_pct_on_purchases' => 0,
            'benefits' => null,
            'active' => true,
        ];
    }

    public function premium(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Premium',
            'slug' => 'premium',
            'monthly_price' => 1080,
            'cans_per_box' => 6,
            'includes_glass_every_n_months' => 3,
        ]);
    }

    public function basic(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Básico',
            'slug' => 'basico',
            'monthly_price' => 350,
            'cans_per_box' => 0,
            'discount_pct_on_purchases' => 29,
        ]);
    }
}
