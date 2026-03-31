<?php

class Response
{
    public static function success(mixed $data, int $status = 200): array
    {
        http_response_code($status);
        return ['success' => true, 'data' => $data];
    }

    public static function error(string $message, int $status = 400): array
    {
        http_response_code($status);
        return ['success' => false, 'error' => $message];
    }
}
