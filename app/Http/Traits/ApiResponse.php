<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\Paginator;

trait ApiResponse
{
    private const JSON_OPTIONS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    protected function success(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        $body = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== []) {
            $body['meta'] = $meta;
        }

        return response()->json($body, $status, [], self::JSON_OPTIONS);
    }

    protected function error(
        string $message,
        int $status = 500,
        array $errors = [],
        array $headers = [],
    ): JsonResponse {
        $body = [
            'success' => false,
            'message' => $message,
            'data' => null,
        ];

        if ($errors !== []) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status, $headers, self::JSON_OPTIONS);
    }

    /**
     * @param  class-string<\Illuminate\Http\Resources\Json\JsonResource>  $resourceClass
     */
    protected function paginated(Paginator $paginator, string $resourceClass, string $message = 'OK'): JsonResponse
    {
        $items = $resourceClass::collection($paginator->items())->resolve(request());

        return $this->success($items, $message, 200, [
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'count' => count($items),
                'has_more' => $paginator->hasMorePages(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ],
        ]);
    }
}
