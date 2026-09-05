<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutValue extends Model
{
    protected $fillable = [
        'about_page_id',
        'number',
        'title',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
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

    public function aboutPage()
    {
        return $this->belongsTo(AboutPage::class);
    }
}
