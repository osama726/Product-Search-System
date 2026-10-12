<?php

namespace App\Actions\Products;

use App\DTOs\ProductSearchData;
use App\Services\ProductSearchService;
use Illuminate\Pagination\Paginator;

final class SearchProductsAction
{
    public function __construct(private readonly ProductSearchService $service) {}

    public function execute(ProductSearchData $data): Paginator
    {
        return $this->service
            ->buildQuery($data)
            ->toBase()
            ->simplePaginate($data->perPage)
            ->withQueryString();
    }
}
