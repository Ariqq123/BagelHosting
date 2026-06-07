<?php

namespace Pterodactyl\BlueprintFramework\Extensions\freeservers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TranslationHelper
{
    private static ?array $translations = null;
    private static ?string $loadedLang = null;

    /**
     * Get a translated string by key.
     * Falls back to English if key not found in current language.
     * Falls back to key itself if not found anywhere.
     */
    public static function t(string $key, ...$args): string
    {
        $lang = self::getLanguage();

        if (self::$loadedLang !== $lang) {
            self::loadTranslations($lang);
        }

        $text = self::$translations[$key] ?? null;

        if ($text === null) {
            // Fallback to English
            $enFile = __DIR__ . '/lang/en.php';
            if (file_exists($enFile)) {
                $en = require $enFile;
                $text = $en[$key] ?? $key;
            } else {
                $text = $key;
            }
        }

        if (!empty($args)) {
            return vsprintf($text, $args);
        }

        return $text;
    }

    /**
     * Get all translations as array (for passing to views/JS).
     */
    public static function all(): array
    {
        $lang = self::getLanguage();
        if (self::$loadedLang !== $lang) {
            self::loadTranslations($lang);
        }
        return self::$translations ?? [];
    }

    /**
     * Get the current language from settings.
     */
    public static function getLanguage(): string
    {
        try {
            if (Schema::hasTable('freeservers_settings')) {
                $settings = DB::table('freeservers_settings')->first();
                if ($settings && !empty($settings->language)) {
                    return $settings->language;
                }
            }
        } catch (\Exception $e) {
            // ignore
        }
        return 'en';
    }

    /**
     * Load translations for a given language.
     */
    private static function loadTranslations(string $lang): void
    {
        $langFile = __DIR__ . '/lang/' . $lang . '.php';

        if (!file_exists($langFile)) {
            $langFile = __DIR__ . '/lang/en.php';
        }

        if (file_exists($langFile)) {
            self::$translations = require $langFile;
        } else {
            self::$translations = [];
        }

        self::$loadedLang = $lang;
    }

    /**
     * Reset cached translations (call after language change).
     */
    public static function reset(): void
    {
        self::$translations = null;
        self::$loadedLang = null;
    }
}
