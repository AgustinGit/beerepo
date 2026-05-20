<?php

declare(strict_types=1);

use App\Models\Plan;

it('creates the premium plan with curated cans and a glass cadence', function () {
    $plan = Plan::factory()->premium()->create();

    expect($plan->slug)->toBe('premium')
        ->and($plan->cans_per_box)->toBe(6)
        ->and($plan->includes_glass_every_n_months)->toBe(3)
        ->and($plan->shipping_included_zones)->toBeArray();
});

it('creates the basic plan with no cans but a purchase discount', function () {
    $plan = Plan::factory()->basic()->create();

    expect($plan->slug)->toBe('basico')
        ->and($plan->cans_per_box)->toBe(0)
        ->and((float) $plan->discount_pct_on_purchases)->toBe(29.0);
});
