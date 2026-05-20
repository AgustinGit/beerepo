<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CustomerSource;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => fake()->numerify('09########'),
            'document_id' => fake()->numerify('#.###.###-#'),
            'marketing_opt_in' => fake()->boolean(70),
            'loyalty_points' => 0,
            'wallet_balance' => 0,
            'source' => fake()->randomElement(CustomerSource::cases()),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }
}
