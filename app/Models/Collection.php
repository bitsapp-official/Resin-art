<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'subtitle',
        'short_description',
        'description',
        'image',
        'cover_image',
        'status',
        'sort_order',
        'is_active',
        'is_featured_on_home',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'is_featured_on_home'  => 'boolean',
        'sort_order'           => 'integer',
    ];

    /**
     * Relationship: A collection has many products (Many-to-Many via pivot table).
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'collection_product')
                    ->withTimestamps();
    }

    protected static function booted()
    {
        static::saving(function ($collection) {
            if ($collection->status === 'ACTIVE') {
                $collection->is_active = true;
            } elseif ($collection->status === 'INACTIVE') {
                $collection->is_active = false;
            }
        });
    }

    /**
     * Scope: Filter active collections for frontend display.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', 'ACTIVE')
              ->orWhere('is_active', true);
        })->where(function ($q) {
            $q->where('status', '!=', 'INACTIVE')
              ->orWhereNull('status');
        });
    }

    /**
     * Helper accessor for effective cover image URL.
     */
    public function getEffectiveCoverImageAttribute(): ?string
    {
        return $this->cover_image ?: $this->image;
    }

    /**
     * Helper accessor for effective short description.
     */
    public function getEffectiveShortDescriptionAttribute(): ?string
    {
        if (!empty($this->short_description)) {
            return $this->short_description;
        }

        if (!empty($this->description)) {
            return Str::limit(strip_tags($this->description), 140);
        }

        return null;
    }

    /**
     * Helper for resolving collection by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
