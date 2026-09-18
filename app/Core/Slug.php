<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Pure string slug generator. No database queries.
 * Turkish transliteration: ç→c, ğ→g, ı→i, ö→o, ş→s, ü→u (both cases).
 */
final class Slug
{
    public static function generate(string $text): string
    {
        $text = trim($text);

        $map = [
            'ç' => 'c', 'Ç' => 'c',
            'ğ' => 'g', 'Ğ' => 'g',
            'ı' => 'i', 'İ' => 'i',
            'ö' => 'o', 'Ö' => 'o',
            'ş' => 's', 'Ş' => 's',
            'ü' => 'u', 'Ü' => 'u',
        ];
        $text = strtr($text, $map);
        $text = mb_strtolower($text, 'UTF-8');
        $text = (string) preg_replace('/[\s_\/\\\\|:;,.+]+/u', '-', $text);
        $text = (string) preg_replace('/[^a-z0-9-]+/u', '-', $text);
        $text = (string) preg_replace('/-+/', '-', $text);

        return trim($text, '-');
    }
}
