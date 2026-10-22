<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\BlogPostStatus;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author_name',
        'status',
        'is_featured_on_home',
        'published_at',
        'seo_title',
        'seo_description',
        'og_image',
    ];

    protected $casts = [
        'status' => BlogPostStatus::class,
        'is_featured_on_home' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function getImageUrlAttribute(): string
    {
        $jewelleryMap = [
            'the-anatomy-of-a-flawless-solitaire' => 'images/catalog/diamond_solitaire_ring.jpg',
            'gold-purity-demystified-14k-vs-18k-vs-22k' => 'images/catalog/temple_gold_bangle.jpg',
            'essential-care-guide-for-diamond-jewellery' => 'images/catalog/diamond_ring_box.jpg',
            'the-art-of-micro-prong-stone-setting' => 'images/catalog/diamond_eternity_band.jpg',
            'commissioning-a-bespoke-engagement-ring' => 'images/catalog/emerald_cut_ring.jpg',
            'inside-the-solitaire-atelier' => 'images/catalog/diamond_tennis_bracelet.jpg',
            7 => 'images/catalog/diamond_solitaire_ring.jpg',
            8 => 'images/catalog/temple_gold_bangle.jpg',
            9 => 'images/catalog/diamond_ring_box.jpg',
            10 => 'images/catalog/diamond_eternity_band.jpg',
            11 => 'images/catalog/emerald_cut_ring.jpg',
            12 => 'images/catalog/diamond_tennis_bracelet.jpg',
        ];

        // If matched to curated jewellery editorial assets, prioritize jewellery
        if (isset($jewelleryMap[$this->slug])) {
            return asset($jewelleryMap[$this->slug]);
        }
        if (isset($jewelleryMap[$this->id])) {
            return asset($jewelleryMap[$this->id]);
        }

        if (!empty($this->featured_image)) {
            // Guard against legacy resin art file references
            $legacyResinNames = ['first_pour', 'tidal_palette', 'living_guide', 'river_table', 'inside_bordeaux', 'bouquet_preserve'];
            $isLegacy = false;
            foreach ($legacyResinNames as $legacyName) {
                if (str_contains($this->featured_image, $legacyName)) {
                    $isLegacy = true;
                    break;
                }
            }

            if (!$isLegacy) {
                if (str_starts_with($this->featured_image, 'http://') || str_starts_with($this->featured_image, 'https://')) {
                    return $this->featured_image;
                }
                if (str_starts_with($this->featured_image, 'images/')) {
                    return asset($this->featured_image);
                }
                if (file_exists(public_path('storage/' . $this->featured_image))) {
                    return asset('storage/' . $this->featured_image);
                }
            }
        }

        return asset('images/catalog/diamond_solitaire_ring.jpg');
    }

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            if (empty($post->slug) && !empty($post->title)) {
                $post->slug = Str::slug($post->title);
            }
        });

        static::saved(function () {
            BlogCategory::clearCache();
        });

        static::deleted(function () {
            BlogCategory::clearCache();
        });
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', BlogPostStatus::PUBLISHED->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
