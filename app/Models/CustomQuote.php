<?php

namespace App\Models;

use App\Enums\CustomQuoteStatus;
use App\Enums\CustomRequestStatus;
use App\Enums\DepositType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomQuote extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => CustomQuoteStatus::class,
        'deposit_type' => DepositType::class,
        'valid_until' => 'datetime',
    ];

    /**
     * Auto-expire quotes when valid_until date has passed & auto-sync request status when quote is sent.
     */
    protected static function booted(): void
    {
        static::retrieved(function ($quote) {
            if ($quote->status === CustomQuoteStatus::SENT && $quote->valid_until && $quote->valid_until->isPast()) {
                $quote->status = CustomQuoteStatus::EXPIRED;
                $quote->saveQuietly();
            }
        });

        static::saved(function ($quote) {
            $statusVal = $quote->status->value ?? $quote->status;
            if ($statusVal === CustomQuoteStatus::SENT->value || $statusVal === 'sent') {
                if ($quote->request) {
                    $quote->request->update(['status' => CustomRequestStatus::QUOTE_SENT]);
                }
            }
        });
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(CustomRequest::class, 'custom_request_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomQuoteItem::class)->orderBy('sort_order');
    }

    public function order(): HasOne
    {
        return $this->hasOne(CustomOrder::class, 'custom_quote_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'QT-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('quote_reference', $reference)->exists());

        return $reference;
    }

    /**
     * Calculate totals based on items.
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('total');
        $this->subtotal = $subtotal;
        $this->total_amount = $subtotal + $this->shipping_amount + $this->tax_amount - $this->discount_amount;
        $this->save();
    }
}
