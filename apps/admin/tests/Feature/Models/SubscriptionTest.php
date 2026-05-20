<?php

declare(strict_types=1);

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionBox;

it('creates an active subscription linked to a customer and plan', function () {
    $subscription = Subscription::factory()->create();

    expect($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->customer)->not->toBeNull()
        ->and($subscription->plan)->not->toBeNull();
});

it('can be paused and cancelled', function () {
    $paused = Subscription::factory()->paused()->create();
    $cancelled = Subscription::factory()->cancelled()->create();

    expect($paused->status)->toBe(SubscriptionStatus::Paused)
        ->and($cancelled->status)->toBe(SubscriptionStatus::Cancelled)
        ->and($cancelled->cancelled_at)->not->toBeNull();
});

it('has subscription boxes', function () {
    $subscription = Subscription::factory()
        ->has(SubscriptionBox::factory()->count(2), 'boxes')
        ->create();

    expect($subscription->boxes)->toHaveCount(2);
});
