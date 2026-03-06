<?php

namespace App\Helper;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class APIResponse
{
    /**
     * Successful response
     */
    public static function success(
        string $message,
        mixed  $data = null,
        int    $statusCode = HttpResponse::HTTP_OK
    ): JsonResponse
    {
        $response = [
            'status' => true,
            'message' => $message,
            'code' => $statusCode
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return Response::json($response, $statusCode);
    }

    /**
     * Error response
     */
    public static function error(
        string $message,
        int    $statusCode = HttpResponse::HTTP_BAD_REQUEST,
        ?array $errors = null
    ): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
            'code' => $statusCode,
            'error'
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return Response::json($response, $statusCode);
    }

    /**
     * Validation error response
     */
    public static function validationError(
        array  $errors,
        string $lang = 'en'
    ): JsonResponse
    {
        $message = $lang === 'ar' ? 'بيانات غير صالحة' : 'Invalid data';
        return self::error($message, HttpResponse::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Resource not found response
     */
    public static function modelNotFound(
        array  $errors = [],
        string $message = null
    ): JsonResponse
    {
        return self::error(
            request()->header('lang', 'ar') === 'ar' ? 'لم يتم العثور على المورد' : 'Resource not found',
            HttpResponse::HTTP_NOT_FOUND,
            $errors
        );
    }

    /**
     * Unauthorized access response
     */
    public static function unauthorized(
        string $message = 'Unauthorized access'
    ): JsonResponse
    {
        return self::error($message, HttpResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Forbidden access response
     */
    public static function forbidden(
        string $message = 'Forbidden access'
    ): JsonResponse
    {
        return self::error($message, HttpResponse::HTTP_FORBIDDEN);
    }
}
