<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeWebhookEvent extends Model
{
    protected $fillable = [
        'stripe_event_id',
        'event_type',
        'payload',
        'processed_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Check if a Stripe event ID has already been recorded and processed.
     */
    public static function hasBeenProcessed(string $stripeEventId): bool
    {
        return static::where('stripe_event_id', $stripeEventId)->exists();
    }

    /**
     * Record and mark an event as processed.
     */
    public static function recordProcessed(string $stripeEventId, string $eventType, ?array $payload = null): self
    {
        return static::firstOrCreate(
            ['stripe_event_id' => $stripeEventId],
            [
                'event_type'   => $eventType,
                'payload'      => $payload,
                'processed_at' => now(),
            ]
        );
    }
}
