<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'sale_price',
        'category_id',
        'collection_id',
        'images',
        'inventory_type',
        'stock',
        'low_stock_threshold',
        'status',
        'is_featured',
        'is_new',
        'is_bestseller',
        'attributes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'images' => 'array',
        'attributes' => 'array',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_bestseller' => 'boolean',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function getImagesAttribute($value): array
    {
        if (empty($value)) {
            return [];
        }
        $images = is_string($value) ? (json_decode($value, true) ?: []) : (array) $value;

        // In Filament Admin Panel, return raw storage paths so FileUpload and ImageColumn resolve files natively on disk
        if (request()->is('admin*') || request()->routeIs('filament.*')) {
            return array_values(array_filter($images));
        }

        // On customer-facing frontend, return web asset URLs (auto-upgrading to WebP when available)
        return array_values(array_filter(array_map(function ($img) {
            if (empty($img)) return '';
            $url = $img;
            if (!str_starts_with($img, 'http://') && !str_starts_with($img, 'https://') && !str_starts_with($img, '/')) {
                $url = asset('storage/' . $img);
            }
            $path = parse_url($url, PHP_URL_PATH);
            if ($path && str_starts_with($path, '/storage/')) {
                $relPath = substr($path, strlen('/storage/'));
                $webpRelPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $relPath);
                if (file_exists(storage_path('app/public/' . $webpRelPath))) {
                    return asset('storage/' . $webpRelPath);
                }
            }
            return $url;
        }, $images)));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->latest();
    }

    public function getAverageRatingAttribute(): float
    {
        $avg = $this->reviews()->avg('rating');
        return $avg ? round($avg, 1) : 5.0;
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    // Helper attribute: effective price
    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->sale_price && $this->sale_price < $this->price ? $this->sale_price : $this->price);
    }

    protected static function booted()
    {
        static::saving(function ($product) {
            // Auto-convert READY_TO_SHIP to MADE_TO_ORDER when stock reaches 0 or below
            if ($product->inventory_type === 'READY_TO_SHIP' && $product->stock <= 0) {
                $product->inventory_type = 'MADE_TO_ORDER';
            }
        });
    }

    // Helper check: availability
    public function getIsAvailableAttribute(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        // Both READY_TO_SHIP and MADE_TO_ORDER are available (READY_TO_SHIP auto-converts to MADE_TO_ORDER when stock is 0)
        return true;
    }

    // Scope for published products
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Relationship: A product belongs to many collections (Pivot: collection_product).
     */
    public function collections(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_product')
                    ->withTimestamps();
    }
}
