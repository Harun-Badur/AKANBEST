<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Document title in the form "<page> | Akan Best Ambalaj", 50–60 characters when the label allows it. */
function seo_title(string $page): string
{
    $page = trim(preg_replace('/\s+/u', ' ', $page) ?? '');
    $suffix = ' | Akan Best Ambalaj';
    $extenders = ['', ' Ürünü', ' Ürünleri', ' — Ambalaj', ' — Toptan Ambalaj', ' — Toptan Ambalajı', ' — Toptan Ambalajlar'];
    $best = $page . $suffix;
    $bestScore = -1;
    foreach ($extenders as $extender) {
        $title = $page . $extender . $suffix;
        $length = mb_strlen($title);
        if ($length > 60) {
            continue;
        }
        $score = $length >= 50 ? 1000 + $length : $length;
        if ($score > $bestScore) {
            $best = $title;
            $bestScore = $score;
        }
    }

    return $best;
}

/** Fallback product description used only when short_description is empty. Shortened past 150 characters. */
function product_meta_description(string $name): string
{
    $text = trim($name) . " — Akan Best Ambalaj'dan toptan ve işletmelere özel ambalaj çözümü. Teklif almak için hemen iletişime geçin.";
    if (mb_strlen($text) <= 150) {
        return $text;
    }

    $cut = mb_substr($text, 0, 150);
    $space = mb_strrpos($cut, ' ');
    if ($space !== false && $space >= 120) {
        $cut = mb_substr($cut, 0, $space);
    }

    return rtrim($cut, " \t.,;:") . '.';
}

/** Pixel size of a public image, or null when the file is missing. */
function image_dimensions(string $publicPath): ?array
{
    $relative = ltrim(str_replace('\\', '/', $publicPath), '/');
    if ($relative === '' || str_contains($relative, '..')) {
        return null;
    }

    $file = BASE_PATH . '/public/' . $relative;
    $info = is_file($file) ? @getimagesize($file) : false;
    if (!is_array($info) || (int) $info[0] < 1 || (int) $info[1] < 1) {
        return null;
    }

    return ['width' => (int) $info[0], 'height' => (int) $info[1]];
}

function img_dimensions_attr(string $publicPath): string
{
    $size = image_dimensions($publicPath);
    if ($size === null) {
        return '';
    }

    return ' width="' . $size['width'] . '" height="' . $size['height'] . '"';
}
