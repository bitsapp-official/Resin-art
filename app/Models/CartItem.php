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

    public function getPrimaryImageUrlAttribute(): ?string
    {
        $color = $this->options['metal_color'] ?? ($this->options['color'] ?? null);
        if ($color && $this->product) {
            $colorImages = $this->product->color_images;
            if (!empty($colorImages[$color])) {
                $img = is_array($colorImages[$color]) ? ($colorImages[$color][0] ?? null) : $colorImages[$color];
                if ($img) return $img;
            }
        }

        if ($this->product?->primary_image_url) {
            return $this->product->primary_image_url;
        }

        if (!empty($this->product?->images[0])) {
            return $this->product->images[0];
        }

        return null;
    }
}
