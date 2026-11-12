<?php

declare(strict_types=1);

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

/**
 * The announcement bar above the header on every page and the banner under
 * the header on the homepage, both switched and filled in by the client in
 * the admin (Beállítások > Sáv és banner, 2026-11-12).
 */
final class PromotionSettings extends Settings
{
    public bool $announcement_enabled;

    public ?string $announcement_text = null;

    public string $announcement_color;

    public ?string $announcement_url = null;

    public bool $banner_enabled;

    public ?string $banner_desktop_image = null;

    public ?string $banner_mobile_image = null;

    public ?string $banner_url = null;

    public ?string $banner_alt = null;

    public static function group(): string
    {
        return 'promotion';
    }

    public function showsAnnouncement(): bool
    {
        return $this->announcement_enabled && filled($this->announcement_text);
    }

    public function showsBanner(): bool
    {
        return $this->banner_enabled && filled($this->banner_desktop_image);
    }

    /**
     * Black or white, whichever reads better on the chosen background
     * (WCAG relative luminance).
     */
    public function announcementTextColor(): string
    {
        $hex = mb_ltrim($this->announcement_color, '#');

        if (mb_strlen($hex) === 3) {
            $hex = preg_replace('/(.)/', '$1$1', $hex);
        }

        if (preg_match('/^[0-9a-fA-F]{6}$/', (string) $hex) !== 1) {
            return '#ffffff';
        }

        $channel = static function (string $pair): float {
            $value = hexdec($pair) / 255;

            return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        };

        $luminance = 0.2126 * $channel(mb_substr($hex, 0, 2)) + 0.7152 * $channel(mb_substr($hex, 2, 2)) + 0.0722 * $channel(mb_substr($hex, 4, 2));

        return $luminance > 0.179 ? '#000000' : '#ffffff';
    }

    public function bannerDesktopUrl(): ?string
    {
        return $this->imageUrl($this->banner_desktop_image);
    }

    /**
     * The phone image, or the desktop one when no phone image was uploaded.
     */
    public function bannerMobileUrl(): ?string
    {
        return $this->imageUrl($this->banner_mobile_image) ?? $this->bannerDesktopUrl();
    }

    private function imageUrl(?string $path): ?string
    {
        return blank($path) ? null : Storage::disk('public')->url($path);
    }
}
