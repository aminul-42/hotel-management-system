<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'group'];

    // Static helper for easy access: Setting::get('vat_percentage')
    public static function get(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    // Helper for image-type settings: Setting::getImageUrl('app_logo')
    public static function getImageUrl(string $key): ?string
    {
        $value = static::get($key);
        return $value ? asset('storage/' . $value) : null;
    }
}