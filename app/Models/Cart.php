<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'total',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function recalculateTotal(): void
    {
        $this->load('items.product');
        $total = 0;
        foreach ($this->items as $item) {
            if ($item->product) {
                // IMPORTANT: Preserve the stored variant price.
                // cart_items.price already has the correct size-variant price
                // set at the time of add-to-cart. Do NOT overwrite with base price.
                // Only use base price as fallback if price is somehow 0 or null.
                $price = (float) $item->price;
                if ($price <= 0) {
                    // Derive from variant options if stored price is missing
                    $price = $item->product->effective_price;
                    $options = $item->options ?? [];
                    if (!empty($options['size'])) {
                        $sizeVariants = $item->product->attributes['size_variants'] ?? [];
                        foreach ($sizeVariants as $variant) {
                            if (($variant['size'] ?? '') === $options['size'] && !empty($variant['price'])) {
                                $price = (float) $variant['price'];
                                break;
                            }
                        }
                    }
                    $item->price = $price;
                    $item->save();
                }
                $total += $price * $item->quantity;
            }
        }
        $this->total = $total;
        $this->save();
    }
}
