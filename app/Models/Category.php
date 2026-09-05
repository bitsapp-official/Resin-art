<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the full URL for the category image.
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->image)) {
            return asset('storage/gallery/segre_river_table.png');
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://') || str_starts_with($this->image, '//')) {
            return $this->image;
        }

        if (str_starts_with($this->image, '/')) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }
}
