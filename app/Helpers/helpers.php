<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Ambil nilai pengaturan soko database, nek ra ono yo balekke default-e.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            $setting = Setting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('format_rupiah')) {
    /**
     * Format angka dadi Rupiah - Ben luwih penak diwoco nggo wong kene.
     */
    function format_rupiah(float|int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
