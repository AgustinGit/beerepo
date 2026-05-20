<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'status',
        'subtotal',
        'shipping_cost',
        'discount_total',
        'total',
        'shipping_address_snapshot',
        'notes',
        'coupon_code',
        'source',
        'mp_payment_id',
        'mp_payment_status',
        'paid_at',
        'delivered_at',
        'delivery_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'source' => OrderSource::class,
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
            'shipping_address_snapshot' => 'array',
            'paid_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
