<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'sku',
        'unit_price',
        'quantity',
        'subtotal',
        'product_snapshot',
        'options',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
        'product_snapshot' => 'array',
        'options' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        $color = $this->options['metal_color'] ?? ($this->options['color'] ?? null);
        if ($color && $this->product) {
            $cImgs = $this->product->color_images;
            if (!empty($cImgs[$color])) {
                $img = is_array($cImgs[$color]) ? ($cImgs[$color][0] ?? null) : $cImgs[$color];
                if ($img) return $img;
            }
        }

        if ($color && !empty($this->product_snapshot['color_images'][$color])) {
            $ci = $this->product_snapshot['color_images'][$color];
            $img = is_array($ci) ? ($ci[0] ?? null) : $ci;
            if ($img) {
                if (!str_starts_with($img, 'http://') && !str_starts_with($img, 'https://') && !str_starts_with($img, '/')) {
                    return asset('storage/' . $img);
                }
                return $img;
            }
        }

        if ($this->product?->primary_image_url) {
            return $this->product->primary_image_url;
        }

        if (!empty($this->product_snapshot['images'][0])) {
            $img = $this->product_snapshot['images'][0];
            if (!str_starts_with($img, 'http://') && !str_starts_with($img, 'https://') && !str_starts_with($img, '/')) {
                return asset('storage/' . $img);
            }
            return $img;
        }

        if (!empty($this->product?->images[0])) {
            return $this->product->images[0];
        }

        return null;
    }
}
