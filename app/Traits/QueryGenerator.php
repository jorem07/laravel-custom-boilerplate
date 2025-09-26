<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait QueryGenerator
{
    public function getJsonResponse(array $data): JsonResponse
    {
        $status = $data['status'] ?? 200;
        return response()->json([
            'message' => $data['message'] ?? null,
            'error' => $data['error'] ?? null,
            'details' => [
                'current_page' => $data['current_page'] ?? null,
                'from' => isset($data['skip']) ? $data['skip'] + 1 : null,
                'to' => $data['to'] ?? null,
                'last_page' => $data['last_page'] ?? null,
                'skip' => $data['skip'] ?? null,
                'take' => $data['take'] ?? null,
                'total' => $data['total'] ?? null,
            ],
            'headers' => $data['headers'] ?? null,
            'body' => $data['body'] ?? null,
            'searchable' => $data['searchable'] ?? null,
            'others' => $data['others'] ?? null,
        ], $status);
    }
}
