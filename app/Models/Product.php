<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

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
        $rawImages = is_string($value) ? (json_decode($value, true) ?: []) : (array) ($value ?? []);
        // Strictly filter out any color photos that might have been stored in the images column
        $rawImages = array_values(array_filter($rawImages, function ($img) {
            return !empty($img) && is_string($img) && !str_starts_with(ltrim($img, '/'), 'products/colors/');
        }));

        // In Filament Admin Panel and Livewire admin updates, return raw storage paths of actual generic images ONLY.
        // Never bleed color-specific photos into the generic images uploader!
        if ((function_exists('filament') && filament()->isServing()) || request()->is('admin*') || request()->routeIs('filament.*') || request()->is('livewire/*')) {
            return $rawImages;
        }

        // On customer-facing frontend, if generic images column is empty, fall back to all images from color_galleries
        $images = $rawImages;
        if (empty($images)) {
            $attrs = $this->jewellery_attributes;
            $colorData = !empty($attrs['color_images']) && is_array($attrs['color_images']) ? $attrs['color_images'] : [];
            $firstColor = $this->metal_colors[0] ?? 'Yellow Gold';
            if (!empty($colorData[$firstColor])) {
                $list = is_array($colorData[$firstColor]) ? $colorData[$firstColor] : [$colorData[$firstColor]];
                foreach ($list as $f) {
                    if (!empty($f) && is_string($f)) $images[] = $f;
                }
            }
            foreach ($colorData as $cName => $val) {
                if ($cName === $firstColor) continue;
                $list = is_array($val) ? $val : [$val];
                foreach ($list as $f) {
                    if (!empty($f) && is_string($f)) $images[] = $f;
                }
            }
            $images = array_values(array_unique(array_filter($images)));
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

    public function getPrimaryImageUrlAttribute(): ?string
    {
        // 1. Check if first metal color (e.g. Yellow Gold) has an image
        $colorImages = $this->color_images;
        if (!empty($colorImages)) {
            $firstColor = $this->metal_colors[0] ?? 'Yellow Gold';
            if (!empty($colorImages[$firstColor])) {
                return $colorImages[$firstColor];
            }
            $first = reset($colorImages);
            if (!empty($first)) {
                return is_string($first) ? $first : reset($first);
            }
        }

        // 2. Fall back to images array
        $images = $this->images;
        return !empty($images) ? $images[0] : null;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->primary_image_url;
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
        $attrs = $this->jewellery_attributes;
        $purities = $this->gold_purities;
        if (!empty($purities) && !empty($attrs['purity_prices'])) {
            $firstPurity = $purities[0];
            if (isset($attrs['purity_prices'][$firstPurity]) && is_numeric($attrs['purity_prices'][$firstPurity])) {
                $val = (float) $attrs['purity_prices'][$firstPurity];
                if ($val > 0) {
                    return $val;
                }
            }
        }

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
        if (array_key_exists('gold_purities', $attrs)) {
            $purities = is_array($attrs['gold_purities']) ? array_values(array_filter($attrs['gold_purities'])) : [];
            $canonicalOrder = ['14KT Gold', '18KT Gold', '22KT Gold'];
            usort($purities, function ($a, $b) use ($canonicalOrder) {
                $posA = array_search($a, $canonicalOrder);
                $posB = array_search($b, $canonicalOrder);
                $idxA = $posA !== false ? $posA : 999;
                $idxB = $posB !== false ? $posB : 999;
                return $idxA <=> $idxB;
            });
            return $purities;
        }
        return ['14KT Gold', '18KT Gold', '22KT Gold'];
    }

    public function getMetalColorsAttribute(): array
    {
        $attrs = $this->jewellery_attributes;
        if (array_key_exists('metal_colors', $attrs)) {
            $colors = is_array($attrs['metal_colors']) ? array_values(array_filter($attrs['metal_colors'])) : [];
            $canonicalOrder = ['Yellow Gold', 'White Gold', 'Rose Gold'];
            usort($colors, function ($a, $b) use ($canonicalOrder) {
                $posA = array_search($a, $canonicalOrder);
                $posB = array_search($b, $canonicalOrder);
                $idxA = $posA !== false ? $posA : 999;
                $idxB = $posB !== false ? $posB : 999;
                return $idxA <=> $idxB;
            });
            return $colors;
        }
        return ['Yellow Gold', 'White Gold', 'Rose Gold'];
    }

    public function getColorGalleriesAttribute(): array
    {
        $attrs = $this->jewellery_attributes;
        $colorData = !empty($attrs['color_images']) ? (array) $attrs['color_images'] : [];

        // In Filament Admin Panel, return raw storage paths
        if (request()->is('admin*') || request()->routeIs('filament.*')) {
            $galleries = [];
            foreach ($colorData as $color => $val) {
                $galleries[$color] = is_array($val) ? array_values(array_filter($val)) : (empty($val) ? [] : [$val]);
            }
            return $galleries;
        }

        // On frontend, resolve each photo to full public URLs
        $resolved = [];
        foreach ($colorData as $color => $val) {
            $list = is_array($val) ? array_values(array_filter($val)) : (empty($val) ? [] : [$val]);
            $resolved[$color] = array_values(array_filter(array_map(function ($imgPath) {
                if (empty($imgPath) || !is_string($imgPath)) return null;
                if (str_starts_with($imgPath, 'http://') || str_starts_with($imgPath, 'https://') || str_starts_with($imgPath, '/')) {
                    $url = $imgPath;
                } else {
                    $url = asset('storage/' . $imgPath);
                }
                $path = parse_url($url, PHP_URL_PATH);
                if ($path && str_starts_with($path, '/storage/')) {
                    $relPath = substr($path, strlen('/storage/'));
                    $webpRelPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $relPath);
                    if (file_exists(storage_path('app/public/' . $webpRelPath))) {
                        $url = asset('storage/' . $webpRelPath);
                    }
                }
                return $url;
            }, $list)));
        }
        return $resolved;
    }

    public function getColorImagesAttribute(): array
    {
        $galleries = $this->color_galleries;
        $primary = [];
        foreach ($galleries as $color => $list) {
            if (!empty($list) && is_array($list)) {
                $primary[$color] = $list[0];
            }
        }
        return $primary;
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
