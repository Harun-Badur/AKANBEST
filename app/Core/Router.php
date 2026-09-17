<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;

final class Router
{
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->register('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->register('POST', $pattern, $handler);
    }

    private function register(string $method, string $pattern, callable $handler): void
    {
        if (!str_starts_with($pattern, '/')) {
            throw new InvalidArgumentException('Route pattern must start with /.');
        }
        $pattern = rtrim($pattern, '/') ?: '/';
        preg_match_all('/\{([A-Za-z_][A-Za-z0-9_]*)\}/', $pattern, $matches, PREG_OFFSET_CAPTURE);
        $names = [];
        $regex = '';
        $offset = 0;
        foreach ($matches[0] as $index => [$placeholder, $position]) {
            $name = $matches[1][$index][0];
            if (in_array($name, $names, true)) {
                throw new InvalidArgumentException('Duplicate route parameter.');
            }
            $names[] = $name;
            $regex .= preg_quote(substr($pattern, $offset, $position - $offset), '~') . '([^/]+)';
            $offset = $position + strlen($placeholder);
        }
        $regex .= preg_quote(substr($pattern, $offset), '~');
        $this->routes[] = [$method, '~^' . $regex . '$~D', $names, $handler];
    }

    public function dispatch(Request $request): Response
    {
        $allowed = [];
        foreach ($this->routes as [$method, $regex, $names, $handler]) {
            if (preg_match($regex, $request->path(), $matches) !== 1) {
                continue;
            }
            $allowed[] = $method;
            if ($method !== $request->method()) {
                continue;
            }
            $params = [];
            foreach ($names as $index => $name) {
                $params[$name] = $matches[$index + 1];
            }
            return $handler($request, $params);
        }

        if ($allowed !== []) {
            return new Response("Method Not Allowed\n", 405, [
                'Content-Type' => 'text/plain; charset=utf-8',
                'Allow' => implode(', ', array_unique($allowed)),
            ]);
        }

        return Response::html(View::render('errors/404'), 404);
    }
}
