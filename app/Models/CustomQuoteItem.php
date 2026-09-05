<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomQuoteItem extends Model
{
    protected $guarded = ['id'];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(CustomQuote::class, 'custom_quote_id');
    }

    protected static function booted()
    {
        static::saving(function ($item) {
            $item->total = $item->quantity * $item->unit_price;
        });

        $recalculateTotals = function ($item) {
            $item->quote->recalculateTotals();
        };

        static::saved($recalculateTotals);
        static::deleted($recalculateTotals);
    }
}
