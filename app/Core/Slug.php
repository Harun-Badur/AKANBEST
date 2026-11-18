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
        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($text, \Normalizer::FORM_C);
            if (is_string($normalized) && $normalized !== '') {
                $text = $normalized;
            }
        }

        $text = mb_strtolower($text, 'UTF-8');
        // İ lowercases to "i" plus a combining dot. Drop marks so that letter stays.
        $text = (string) preg_replace('/\p{M}+/u', '', $text);
        $text = strtr($text, [
            'ç' => 'c',
            'ğ' => 'g',
            'ı' => 'i',
            'ö' => 'o',
            'ş' => 's',
            'ü' => 'u',
        ]);
        $text = (string) preg_replace('/[\s_\/\\\\|:;,.+]+/u', '-', $text);
        $text = (string) preg_replace('/[^a-z0-9-]+/u', '-', $text);
        $text = (string) preg_replace('/-+/', '-', $text);

        return trim($text, '-');
    }
}
