<?php

namespace App\Models;

use App\Enums\CustomOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomOrder extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => CustomOrderStatus::class,
        'shipping_date' => 'date',
        'delivered_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(CustomRequest::class, 'custom_request_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(CustomQuote::class, 'custom_quote_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function designs(): HasMany
    {
        return $this->hasMany(CustomOrderDesign::class)->orderByDesc('version');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(CustomOrderUpdate::class)->latest();
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'CO-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('order_reference', $reference)->exists());

        return $reference;
    }
}
