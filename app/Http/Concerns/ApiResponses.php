<?php

namespace App\Http\Concerns;

use Illuminate\Http\JsonResponse;

/**
 * The consumer API envelope: keys are always data, message, errors, okay,
 * in that order.
 */
trait ApiResponses
{
    /**
     * @param  array<mixed>  $data
     */
    protected function apiOk(array $data = [], string $message = ''): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'errors' => [],
            'okay' => true,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * @param  array<mixed>  $errors
     */
    protected function apiFail(
        string $message,
        array $errors = [],
        int $status = JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
    ): JsonResponse {
        return response()->json([
            'data' => [],
            'message' => $message,
            'errors' => $errors,
            'okay' => false,
        ], $status);
    }
}
