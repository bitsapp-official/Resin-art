<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'invoice_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'items'        => 'array',
    ];

    public function setPaidAmountAttribute($value): void
    {
        $this->attributes['paid_amount'] = (float) ($value ?? 0);
    }

    public function setTotalAmountAttribute($value): void
    {
        $this->attributes['total_amount'] = (float) ($value ?? 0);
    }

    public function customRequest(): BelongsTo
    {
        return $this->belongsTo(CustomRequest::class, 'custom_request_id');
    }

    /**
     * Generate unique Invoice / Receipt Number (REC-2026-XXXXX)
     */
    public static function generateNumber(): string
    {
        do {
            $number = 'REC-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('invoice_number', $number)->exists());

        return $number;
    }
}
