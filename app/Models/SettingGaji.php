<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SettingGaji extends Model
{
    use HasFactory;

    protected static ?array $gajiPokokCache = null;

    protected $table = 'setting_gajis';

    protected $fillable = [
        'jabatan',
        'nama_jabatan',
        'gaji_pokok',
    ];

    protected $casts = [
        'gaji_pokok' => 'decimal:2',
    ];

    public static function defaultSettings(): array
    {
        return config('gaji.jabatan', []);
    }

    public static function ensureDefaultsExist(): void
    {
        if (!Schema::hasTable('setting_gajis')) {
            return;
        }

        foreach (self::defaultSettings() as $jabatan => $setting) {
            self::query()->firstOrCreate(
                ['jabatan' => $jabatan],
                [
                    'nama_jabatan' => $setting['label'] ?? ucwords(str_replace('_', ' ', $jabatan)),
                    'gaji_pokok' => $setting['gaji_pokok'] ?? config('gaji.default_gaji_pokok', 0),
                ]
            );
        }
    }

    public static function getGajiPokokForJabatan(string $jabatan): int
    {
        if (Schema::hasTable('setting_gajis')) {
            if (self::$gajiPokokCache === null) {
                self::ensureDefaultsExist();

                self::$gajiPokokCache = self::query()
                    ->pluck('gaji_pokok', 'jabatan')
                    ->map(fn ($value) => (int) $value)
                    ->all();
            }

            if (array_key_exists($jabatan, self::$gajiPokokCache)) {
                return self::$gajiPokokCache[$jabatan];
            }
        }

        return (int) config(
            "gaji.jabatan.{$jabatan}.gaji_pokok",
            config('gaji.default_gaji_pokok', 0)
        );
    }

    public static function clearCache(): void
    {
        self::$gajiPokokCache = null;
    }
}
