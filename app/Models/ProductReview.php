<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'reviewer_name',
        'rating',
        'title',
        'comment',
        'is_verified_buyer',
        'is_approved',
        'is_featured_on_home',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_buyer' => 'boolean',
        'is_approved' => 'boolean',
        'is_featured_on_home' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
