<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'label' => fake()->randomElement(['Casa', 'Trabajo', 'Otra']),
            'recipient_name' => fake()->name(),
            'street' => fake()->streetName(),
            'number' => (string) fake()->buildingNumber(),
            'apt' => null,
            'neighborhood' => fake()->citySuffix(),
            'city' => 'Montevideo',
            'department' => 'Montevideo',
            'postal_code' => fake()->numerify('#####'),
            'country' => 'UY',
            'lat' => fake()->latitude(-35, -34),
            'lng' => fake()->longitude(-56, -55),
            'notes_delivery' => null,
        ];
    }
}
