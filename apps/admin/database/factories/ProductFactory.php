<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' '.fake()->randomElement(['IPA', 'APA', 'Stout', 'Lager']);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'recipe_id' => null,
            'description' => fake()->sentence(),
            'retail_price' => fake()->randomElement([180, 220]),
            'wholesale_price' => fake()->randomElement([128, 156]),
            'image_url' => null,
            'gallery' => null,
            'active' => true,
            'featured' => false,
        ];
    }
}
