<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'monthly_price',
        'annual_discount_pct',
        'cans_per_box',
        'includes_glass_every_n_months',
        'shipping_included_zones',
        'discount_pct_on_purchases',
        'benefits',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'annual_discount_pct' => 'decimal:2',
            'cans_per_box' => 'integer',
            'includes_glass_every_n_months' => 'integer',
            'shipping_included_zones' => 'array',
            'discount_pct_on_purchases' => 'decimal:2',
            'benefits' => 'array',
            'active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
