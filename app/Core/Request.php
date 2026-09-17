<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(
        private readonly array $server = [],
        private readonly array $query = [],
        private readonly array $post = [],
    ) {
    }

    public static function capture(): self
    {
        return new self($_SERVER, $_GET, $_POST);
    }

    public function method(): string
    {
        return strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    public function path(): string
    {
        $path = parse_url((string) ($this->server['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        if (!is_string($path)) {
            return '';
        }

        $path = rawurldecode($path);
        return rtrim($path, '/') ?: '/';
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->query : ($this->query[$key] ?? $default);
    }

    public function post(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->post : ($this->post[$key] ?? $default);
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $key = strtoupper(str_replace('-', '_', $name));
        if (!in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
            $key = 'HTTP_' . $key;
        }

        return isset($this->server[$key]) ? (string) $this->server[$key] : $default;
    }

    public function ip(): ?string
    {
        // X-Forwarded-* headers are untrusted; only the direct REMOTE_ADDR is used.
        return isset($this->server['REMOTE_ADDR']) ? (string) $this->server['REMOTE_ADDR'] : null;
    }
}
