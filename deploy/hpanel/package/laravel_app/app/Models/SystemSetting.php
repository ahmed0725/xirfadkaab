<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'logo_path',
        'school_name',
        'address',
        'contact_info',
    ];

    /**
     * Load the single current settings row (create defaults if missing).
     */
    public static function current(): self
    {
        $existing = static::query()->first();
        if ($existing) {
            return $existing;
        }

        $defaultLogoPath = null;
        if (file_exists(public_path('xirfadkaablogo.jpg'))) {
            $defaultLogoPath = 'xirfadkaablogo.jpg';
        }

        return static::query()->create([
            'logo_path' => $defaultLogoPath,
            'school_name' => 'Xirfad Kaab',
            'address' => '',
            'contact_info' => '',
        ]);
    }
}

