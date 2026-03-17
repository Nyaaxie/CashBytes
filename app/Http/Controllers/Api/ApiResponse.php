<?php

namespace App\Http\Controllers\Api;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Success', int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function error(string $message = 'Error', int $status = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    protected function unauthorized(string $message = 'Unauthorized')
    {
        return $this->error($message, 401);
    }

    protected function notFound(string $message = 'Not found')
    {
        return $this->error($message, 404);
    }

    protected function validationError($errors)
    {
        return $this->error('Validation failed.', 422, $errors);
    }
}