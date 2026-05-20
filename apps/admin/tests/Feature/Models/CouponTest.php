<?php

declare(strict_types=1);

use App\Enums\CouponApplicableTo;
use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\QueryException;

it('creates a percentage coupon', function () {
    $coupon = Coupon::factory()->create(['code' => 'BIENVENIDA']);

    expect($coupon->type)->toBe(CouponType::Percent)
        ->and($coupon->applicable_to)->toBe(CouponApplicableTo::All)
        ->and($coupon->active)->toBeTrue();

    $this->assertDatabaseHas('coupons', ['code' => 'BIENVENIDA']);
});

it('creates a fixed-amount coupon', function () {
    $coupon = Coupon::factory()->fixed()->create();

    expect($coupon->type)->toBe(CouponType::Fixed)
        ->and((float) $coupon->value)->toBe(100.0);
});

it('enforces unique coupon codes', function () {
    Coupon::factory()->create(['code' => 'DUP']);

    Coupon::factory()->create(['code' => 'DUP']);
})->throws(QueryException::class);
