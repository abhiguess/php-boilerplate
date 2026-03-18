<?php

class Request
{
    /**
     * Get input from GET, POST, or JSON body
     */
    public static function input(string $key, mixed $default = null): mixed
    {
        // Check POST first, then GET
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        // Check JSON body
        $json = self::json();
        return $json[$key] ?? $default;
    }

    /**
     * Get all input data
     */
    public static function all(): array
    {
        $data = array_merge($_GET, $_POST);
        $json = self::json();
        if ($json) {
            $data = array_merge($data, $json);
        }
        return $data;
    }

    /**
     * Get only specified keys
     */
    public static function only(array $keys): array
    {
        $all = self::all();
        return array_intersect_key($all, array_flip($keys));
    }

    /**
     * Parse JSON request body
     */
    public static function json(): array
    {
        static $parsed = null;
        if ($parsed === null) {
            $raw = file_get_contents('php://input');
            $parsed = json_decode($raw, true) ?? [];
        }
        return $parsed;
    }

    /**
     * Check if request is AJAX/API
     */
    public static function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    /**
     * Get request method
     */
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get uploaded file
     */
    public static function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Get query string parameter
     */
    public static function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }
}
