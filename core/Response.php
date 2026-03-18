<?php

class Response
{
    /**
     * Send JSON response
     */
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send success JSON
     */
    public static function success(mixed $data = null, string $message = 'Success'): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    /**
     * Send error JSON
     */
    public static function error(string $message = 'Error', int $status = 400, array $errors = []): void
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors) {
            $payload['errors'] = $errors;
        }
        self::json($payload, $status);
    }
}
