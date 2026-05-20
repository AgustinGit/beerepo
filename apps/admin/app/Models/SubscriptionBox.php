<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SubscriptionBoxFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionBox extends Model
{
    /** @use HasFactory<SubscriptionBoxFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'subscription_id',
        'billing_cycle',
        'scheduled_send_date',
        'sent_date',
        'contents',
        'includes_glass',
        'shipping_address_snapshot',
        'delivery_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'billing_cycle' => 'integer',
            'scheduled_send_date' => 'date',
            'sent_date' => 'date',
            'contents' => 'array',
            'includes_glass' => 'boolean',
            'shipping_address_snapshot' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
