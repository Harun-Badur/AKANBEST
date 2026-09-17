<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        if (preg_match('~^[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+)*$~D', $template) !== 1) {
            throw new RuntimeException('Invalid template name.');
        }

        $root = realpath(BASE_PATH . '/templates');
        $file = realpath(BASE_PATH . '/templates/' . $template . '.php');
        if ($root === false || $file === false || !is_file($file)
            || !str_starts_with($file, $root . DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Template not found: ' . $template);
        }

        return (static function (string $__file, array $__data): string {
            $__level = ob_get_level();
            ob_start();
            try {
                extract($__data, EXTR_SKIP);
                include $__file;
                return (string) ob_get_clean();
            } catch (Throwable $exception) {
                while (ob_get_level() > $__level) {
                    ob_end_clean();
                }
                throw $exception;
            }
        })($file, $data);
    }
}
