<?php

class ApiController
{
    /**
     * Send success response
     */
    protected function success(mixed $data = null, string $message = 'Success', int $status = 200): void
    {
        Response::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Send created response (201)
     */
    protected function created(mixed $data = null, string $message = 'Created successfully'): void
    {
        $this->success($data, $message, 201);
    }

    /**
     * Send error response
     */
    protected function error(string $message = 'Error', int $status = 400, array $errors = []): void
    {
        Response::error($message, $status, $errors);
    }

    /**
     * Send not found response
     */
    protected function notFound(string $message = 'Resource not found'): void
    {
        $this->error($message, 404);
    }

    /**
     * Validate request and return errors if failed, null if passed
     */
    protected function validate(array $data, array $rules): ?array
    {
        $v = Validator::make($data, $rules);
        if ($v->fails()) {
            $this->error('Validation failed', 422, $v->errors());
            return null; // never reached (Response::error exits)
        }
        return $data;
    }
}
