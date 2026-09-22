<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * Product image pipeline: content-based validation (finfo + GD double check)
 * then resize to WebP. Filename never derives from user input; extension is
 * always the fixed output suffix .webp.
 */
final class ImageUploader
{
    public const MAX_BYTES = 5242880; // 5 MB
    public const MAX_DIMENSION = 4000; // pixels per side
    public const MAX_WIDTH = 1600; // output width cap, aspect preserved
    public const WEBP_QUALITY = 82;
    public const UPLOAD_DIR = BASE_PATH . '/public/uploads/products';
    public const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * Validates a $_FILES element. Returns [true, ''] or [false, reason].
     * Reason strings are safe for flash display.
     *
     * @return array{0: bool, 1: string}
     */
    public static function validateUpload(mixed $file): array
    {
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $file === null || (is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)
                ? [false, 'no-file']
                : [false, 'Dosya yüklenemedi.'];
        }
        if (!is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            return [false, 'Dosya yüklenemedi.'];
        }

        return self::validatePath((string) $file['tmp_name'], (int) ($file['size'] ?? 0));
    }

    /**
     * Content validation on a filesystem path. Testable core (no upload state).
     * Never trusts the filename or its extension.
     *
     * @return array{0: bool, 1: string}
     */
    public static function validatePath(string $path, int $size): array
    {
        if ($size <= 0 || $size > self::MAX_BYTES) {
            return [false, 'Dosya boyutu en fazla 5 MB olabilir.'];
        }
        if (!is_file($path) || !is_readable($path)) {
            return [false, 'Dosya okunamadı.'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($path);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return [false, 'Yalnızca JPEG, PNG veya WebP kabul edilir.'];
        }

        $raw = @file_get_contents($path);
        if (!is_string($raw) || $raw === '') {
            return [false, 'Dosya okunamadı.'];
        }
        $image = @imagecreatefromstring($raw);
        if ($image === false) {
            return [false, 'Görsel içeriği geçersiz.'];
        }
        $width = imagesx($image);
        $height = imagesy($image);
        imagedestroy($image);
        if ($width <= 0 || $height <= 0 || $width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
            return [false, 'Boyut en fazla 4000×4000 piksel olabilir.'];
        }

        return [true, ''];
    }

    /**
     * Converts a validated tmp file to WebP (max width 1600, aspect kept).
     * Returns the relative path 'uploads/products/product_<id>_<hex>.webp'.
     */
    public static function store(string $tmpPath, int $productId): string
    {
        if ($productId < 1) {
            throw new RuntimeException('Geçersiz ürün.');
        }
        [$ok, $reason] = self::validatePath($tmpPath, (int) @filesize($tmpPath));
        if (!$ok) {
            throw new RuntimeException($reason);
        }

        $raw = (string) file_get_contents($tmpPath);
        $src = imagecreatefromstring($raw);
        if ($src === false) {
            throw new RuntimeException('Görsel işlenemedi.');
        }

        $width = imagesx($src);
        $height = imagesy($src);
        $out = $src;
        if ($width > self::MAX_WIDTH) {
            $newWidth = self::MAX_WIDTH;
            $newHeight = (int) round($height * ($newWidth / $width));
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($src);
            $out = $resized;
        }

        if (!is_dir(self::UPLOAD_DIR) && !@mkdir(self::UPLOAD_DIR, 0775, true) && !is_dir(self::UPLOAD_DIR)) {
            imagedestroy($out);
            throw new RuntimeException('Yükleme dizini oluşturulamadı.');
        }

        $name = 'product_' . $productId . '_' . bin2hex(random_bytes(8)) . '.webp';
        $target = self::UPLOAD_DIR . '/' . $name;
        $saved = @imagewebp($out, $target, self::WEBP_QUALITY);
        imagedestroy($out);
        if ($saved !== true) {
            throw new RuntimeException('WebP kaydı başarısız.');
        }

        return 'uploads/products/' . $name;
    }
}
