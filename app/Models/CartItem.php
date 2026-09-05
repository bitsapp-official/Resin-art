<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_name',
        'sku',
        'quantity',
        'price',
        'options',
        'subtotal',
    ];

    public function getProductNameAttribute(): string
    {
        return $this->attributes['product_name'] ?? ($this->product?->name ?? 'Resin Piece');
    }

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'options' => 'array',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->price * $this->quantity);
    }
}
