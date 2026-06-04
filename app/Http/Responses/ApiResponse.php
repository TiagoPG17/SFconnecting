<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = '', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function created(mixed $data = null, string $message = 'Creado exitosamente.'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    public static function error(string $message, array $errors = [], int $status = 422): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }

    public static function notFound(string $message = 'Recurso no encontrado.'): JsonResponse
    {
        return self::error($message, [], 404);
    }

    public static function forbidden(string $message = 'Acceso denegado.'): JsonResponse
    {
        return self::error($message, [], 403);
    }

    public static function serverError(string $message = 'Error interno del servidor.'): JsonResponse
    {
        return self::error($message, [], 500);
    }
}
