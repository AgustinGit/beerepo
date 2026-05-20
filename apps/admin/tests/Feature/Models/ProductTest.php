<?php

declare(strict_types=1);

use App\Models\Product;

it('creates a product with retail and wholesale prices', function () {
    $product = Product::factory()->create([
        'name' => 'Lupulada Test',
        'slug' => 'lupulada-test',
    ]);

    expect($product->exists)->toBeTrue()
        ->and($product->active)->toBeTrue()
        ->and($product->retail_price)->not->toBeNull()
        ->and($product->wholesale_price)->not->toBeNull();

    $this->assertDatabaseHas('products', ['slug' => 'lupulada-test']);
});
