<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'label',
        'type',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('non_contact_group', function ($builder) {
            $builder->where('group', '!=', 'contact');
        });
    }

    private static array $cache = [];

    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key] ?? $default;
        }

        try {
            $setting = static::withoutGlobalScopes()->where('key', $key)->first();
            $val = $setting ? $setting->value : null;
            static::$cache[$key] = $val;
            return $val ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, ?string $value, string $group = 'general', ?string $label = null, string $type = 'text'): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'label' => $label ?? title_case(str_replace('_', ' ', $key)),
                'type' => $type,
            ]
        );

        static::$cache[$key] = $value;

        return $setting;
    }
}
