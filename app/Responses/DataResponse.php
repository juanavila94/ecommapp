<?php

namespace App\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use JustSteveKing\StatusCode\Http;

class DataResponse implements Responsable
{
    public function __construct(
        public readonly mixed $data,
        public readonly mixed $errors,
        public readonly Http $status = Http::OK
    ) {
    }

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'success' => $this->status->value === 200 || 201,
            'errors' => $this->errors,
            'data' => $this->data,
        ]);
    }
}
