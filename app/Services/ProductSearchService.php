<?php

namespace App\Services;

use App\DTOs\ProductSearchData;
use App\Enums\ProductSortField;
use App\Models\Product;
use App\Support\SearchTermParser;
use Illuminate\Database\Eloquent\Builder;

class ProductSearchService
{
    /**
     * Builds a query for searching products based on the provided search data.
     * */
    private const COLUMNS = [
        'products.id',
        'products.name',
        'products.price',
        'products.is_available',
        'products.created_at',
        'categories.id as category_id',
        'categories.name as category_name',
        'categories.slug as category_slug',
    ];

    public function buildQuery(ProductSearchData $data): Builder
    {
        $parser = $data->query !== null ? new SearchTermParser($data->query) : null;
        $booleanQuery = $parser?->toBooleanQuery();

        $query = Product::query()
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->select(self::COLUMNS)
            // Select the first 200 characters of the description to avoid fetching large text fields unnecessarily
            ->selectRaw('LEFT(products.description, 200) AS description');

        $this->applyTextSearch($query, $parser, $booleanQuery);
        $this->applyFilters($query, $data);
        $this->applySorting($query, $data, $booleanQuery);

        return $query;
    }

    private function applyTextSearch(Builder $query, ?SearchTermParser $parser, ?string $booleanQuery): void
    {
        if ($parser === null || $parser->isEmpty()) {
            return;
        }

        if ($booleanQuery !== null) {
            $query->matchingFullText($booleanQuery);

            return;
        }

        $likeTerms = $parser->toLikeTerms();

        $query->where(function (Builder $likeQuery) use ($likeTerms) {
            foreach ($likeTerms as $term) {
                $likeQuery->orWhere(function (Builder $subQuery) use ($term) {
                    $subQuery->where('products.name', 'like', $term)
                        ->orWhere('products.description', 'like', $term);
                });
            }
        });
    }

    private function applyFilters(Builder $query, ProductSearchData $data): void
    {
        $query
            ->when($data->categoryId !== null, fn (Builder $q) => $q->inCategory($data->categoryId))
            ->priceBetween($data->minPrice, $data->maxPrice)
            ->when($data->onlyAvailable, fn (Builder $q) => $q->available());
    }

    private function applySorting(Builder $query, ProductSearchData $data, ?string $booleanQuery): void
    {
        $direction = $data->direction->value;

        if ($data->sort === ProductSortField::Relevance && $booleanQuery !== null) {
            // Select the relevance score and order by it in descending order
            $query->selectRaw('MATCH(products.name, products.description) AGAINST (? IN BOOLEAN MODE) AS relevance', [$booleanQuery])
                ->orderByDesc('relevance');
        } elseif ($data->sort === ProductSortField::Price) {
            $query->orderBy('products.price', $direction);
        } else {
            $query->orderBy('products.created_at', $direction);
        }

        // order by ID as a tiebreaker to ensure consistent ordering
        $query->orderBy('products.id', $direction);
    }
}
