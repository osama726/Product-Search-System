<?php

namespace App\DTOs;

use App\Enums\ProductSortField;
use App\Enums\SortDirection;

final readonly class ProductSearchData
{
    public function __construct(
        public ?string $query,
        public ?string $minPrice,
        public ?string $maxPrice,
        public ?int $categoryId,
        public bool $onlyAvailable,
        public ProductSortField $sort,
        public SortDirection $direction,
        public int $perPage,
        public int $page = 1,
    ) {}
}
