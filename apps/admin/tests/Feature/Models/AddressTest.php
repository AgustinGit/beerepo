<?php

declare(strict_types=1);

use App\Models\Address;

it('creates an address belonging to a customer', function () {
    $address = Address::factory()->create([
        'city' => 'Montevideo',
        'department' => 'Montevideo',
    ]);

    expect($address->exists)->toBeTrue()
        ->and($address->customer)->not->toBeNull()
        ->and($address->country)->toBe('UY');

    $this->assertDatabaseHas('addresses', ['id' => $address->id]);
});
