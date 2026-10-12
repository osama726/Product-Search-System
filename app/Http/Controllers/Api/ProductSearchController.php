<?php

namespace App\Http\Controllers\Api;

use App\Actions\Products\SearchProductsAction;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\ProductSearchRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;

class ProductSearchController extends ApiController
{
    public function __invoke(ProductSearchRequest $request, SearchProductsAction $action): JsonResponse
    {
        return $this->paginated(
            $action($request->toData()),
            ProductResource::class,
            'Products retrieved successfully.'
        );
    }
}
