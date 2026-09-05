<?php

namespace App\Models;

use App\Enums\CustomRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => CustomRequestStatus::class,
        'required_date' => 'date',
        'submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CustomRequestImage::class)->orderBy('sort_order');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CustomRequestMessage::class)->latest();
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(CustomQuote::class)->latest();
    }

    public function activeQuote(): HasOne
    {
        return $this->hasOne(CustomQuote::class)->latestOfMany();
    }

    public function order(): HasOne
    {
        return $this->hasOne(CustomOrder::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    /**
     * Generate a secure unique public reference.
     */
    public static function generateReference(): string
    {
        do {
            $reference = 'CR-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('public_reference', $reference)->exists());

        return $reference;
    }
}
