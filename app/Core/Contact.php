<?php

declare(strict_types=1);

namespace App\Core;

/** Public contact details from config. Empty values stay empty. */
final class Contact
{
    public static function phone(): string
    {
        return self::clean((string) Config::get('contact.phone', ''));
    }

    public static function email(): string
    {
        return self::clean((string) Config::get('contact.email', ''));
    }

    public static function address(): string
    {
        return self::clean((string) Config::get('contact.address', ''));
    }

    public static function whatsappDigits(): string
    {
        $digits = preg_replace('/\D+/', '', self::clean((string) Config::get('contact.whatsapp', ''))) ?? '';
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }

    public static function whatsappUrl(string $text): string
    {
        $digits = self::whatsappDigits();
        if ($digits === '') {
            return '';
        }

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($text);
    }

    private static function clean(string $value): string
    {
        return trim(str_replace(["\0", "\r", "\n"], '', $value));
    }
}
