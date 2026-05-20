<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CouponApplicableTo;
use App\Enums\CouponType;
use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'used_count',
        'min_order_total',
        'applicable_to',
        'starts_at',
        'expires_at',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'applicable_to' => CouponApplicableTo::class,
            'value' => 'decimal:2',
            'max_uses' => 'integer',
            'used_count' => 'integer',
            'min_order_total' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'active' => 'boolean',
        ];
    }
}
