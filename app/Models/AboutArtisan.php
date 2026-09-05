<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AboutArtisan extends Model
{
    use HasFactory;

    protected $fillable = [
        'about_page_id',
        'name',
        'role',
        'image_path',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            AboutPage::clearCache();
        });

        static::deleted(function () {
            AboutPage::clearCache();
        });
    }

    public function aboutPage(): BelongsTo
    {
        return $this->belongsTo(AboutPage::class);
    }
}
