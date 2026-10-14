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
        'video_path',
        'video_url',
        'video_thumbnail',
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

    public function getJewelleryAttributesAttribute(): array
    {
        $raw = $this->getAttribute('attributes');
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw)) {
            return json_decode($raw, true) ?: [];
        }
        return [];
    }

    public function getGoldPuritiesAttribute(): array
    {
        $attrs = $this->jewellery_attributes;
        return !empty($attrs['gold_purities']) ? (array) $attrs['gold_purities'] : ['14KT Gold', '18KT Gold', '22KT Gold'];
    }

    public function getMetalColorsAttribute(): array
    {
        $attrs = $this->jewellery_attributes;
        return !empty($attrs['metal_colors']) ? (array) $attrs['metal_colors'] : ['Yellow Gold', 'White Gold', 'Rose Gold'];
    }

    public function getSizeLabelAttribute(): string
    {
        $attrs = $this->jewellery_attributes;
        return !empty($attrs['size_label']) ? $attrs['size_label'] : 'Size';
    }

    public function getDiamondCaratAttribute(): ?string
    {
        return $this->jewellery_attributes['diamond_carat'] ?? null;
    }

    public function getDiamondColorClarityAttribute(): ?string
    {
        return $this->jewellery_attributes['diamond_color_clarity'] ?? null;
    }

    public function getCertificationAttribute(): string
    {
        return $this->jewellery_attributes['certification'] ?? '100% Certified Natural Diamonds';
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

    /**
     * Determine if the product has a video attached (uploaded or URL).
     */
    public function getHasVideoAttribute(): bool
    {
        return !empty($this->video_path) || !empty($this->video_url);
    }

    /**
     * Resolve the playable video URL (local storage asset takes precedence over external link).
     */
    public function getVideoAssetUrlAttribute(): ?string
    {
        if (!empty($this->video_path)) {
            return asset('storage/' . $this->video_path);
        }

        if (!empty($this->video_url)) {
            return $this->video_url;
        }

        return null;
    }

    /**
     * Extract YouTube Video ID from any standard YouTube URL (watch, shorts, embed, youtu.be).
     */
    public function getYoutubeIdAttribute(): ?string
    {
        if (empty($this->video_url)) {
            return null;
        }

        $pattern = '/(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:embed\/|v\/|shorts\/|watch\?(?:.*&)?v=))([\w-]{11})/i';
        if (preg_match($pattern, trim($this->video_url), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Return standard YouTube embed URL with optimal parameters.
     */
    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        $ytId = $this->youtube_id;
        if ($ytId) {
            return "https://www.youtube.com/embed/{$ytId}?rel=0&playsinline=1&enablejsapi=1";
        }
        return null;
    }

    /**
     * Extract Vimeo Video ID if applicable.
     */
    public function getVimeoIdAttribute(): ?string
    {
        if (empty($this->video_url)) {
            return null;
        }

        if (preg_match('/(?:vimeo\.com\/(?:video\/)?|player\.vimeo\.com\/video\/)(\d+)/i', trim($this->video_url), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Return Vimeo embed URL if applicable.
     */
    public function getVimeoEmbedUrlAttribute(): ?string
    {
        $vimeoId = $this->vimeo_id;
        if ($vimeoId) {
            return "https://player.vimeo.com/video/{$vimeoId}?dnt=1&playsinline=1";
        }
        return null;
    }

    /**
     * Resolve thumbnail / poster for the video automatically.
     * YouTube and Vimeo thumbnails are resolved from their APIs.
     * For uploaded videos without custom thumbnail, returns null so the player renders the native video frame.
     */
    public function getVideoThumbnailUrlAttribute(): ?string
    {
        if (!empty($this->video_thumbnail)) {
            return asset('storage/' . $this->video_thumbnail);
        }

        if ($this->youtube_id) {
            return "https://img.youtube.com/vi/{$this->youtube_id}/hqdefault.jpg";
        }

        if ($this->vimeo_id) {
            return "https://vumbnail.com/{$this->vimeo_id}.jpg";
        }

        return null;
    }
}
