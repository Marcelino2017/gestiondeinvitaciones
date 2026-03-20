<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CustomException extends Exception
{
    public function __construct(
        string $message = 'Error interno del servidor.',
        int $status = 500,
        private readonly mixed $errors = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }

    public function status(): int
    {
        return $this->getCode() > 0 ? $this->getCode() : 500;
    }

    public function render(Request $request): JsonResponse
    {
        Log::error($this->getMessage(), [
            'status' => $this->status(),
            'path' => $request->path(),
        ]);

        return response()->json([
            'success' => false,
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'data' => null,
            'errors' => $this->errors,
        ], $this->status());
    }
}
