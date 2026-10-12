<?php

namespace App\Exceptions;

use App\Http\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ApiExceptionHandler
{
    use ApiResponse;

    public function register(Exceptions $exceptions): void
    {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $this->isApi($request)) {
                return null;
            }

            return $this->error('The given data was invalid.', 422, $e->errors());
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return $this->isApi($request) ? $this->error('Unauthenticated.', 401) : null;
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            return $this->isApi($request) ? $this->error('Resource not found.', 404) : null;
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $this->isApi($request)) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = $status === 404
                    ? 'Resource not found.'
                    : ($e->getMessage() ?: (Response::$statusTexts[$status] ?? 'Error'));

                return $this->error($message, $status, [], $e->getHeaders());
            }

            return $this->error(config('app.debug') ? $e->getMessage() : 'Server error.', 500);
        });
    }

    private function isApi(Request $request): bool
    {
        return $request->is('api/*');
    }
}
