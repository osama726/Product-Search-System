<?php

namespace App\Actions\Products;

use App\DTOs\ProductSearchData;
use App\Services\ProductSearchService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

final class SearchProductsAction
{
    private const CACHE_TTL_SECONDS = 300;

    public function __construct(private readonly ProductSearchService $service) {}

    public static function cacheKey(ProductSearchData $data): string
    {
        $payload = [
            'q' => $data->query,
            'min_price' => $data->minPrice,
            'max_price' => $data->maxPrice,
            'category_id' => $data->categoryId,
            'only_available' => $data->onlyAvailable ? 1 : 0,
            'sort' => $data->sort->value,
            'direction' => $data->direction->value,
            'per_page' => $data->perPage,
            'page' => $data->page,
        ];

        return 'product_search:'.md5(json_encode($payload, JSON_THROW_ON_ERROR));
    }

    public function __invoke(ProductSearchData $data): Paginator
    {
        $key = self::cacheKey($data);

        $cached = Cache::get($key);

        if (is_array($cached) && array_key_exists('items', $cached)) {
            $paginator = new Paginator(
                array_map(
                    static fn (mixed $item): object => (object) $item,
                    $cached['items']
                ),
                $cached['per_page'],
                $cached['current_page'] ?? 1,
            );

            return $paginator->withQueryString();
        }

        $paginator = $this->service
            ->buildQuery($data)
            ->toBase()
            ->simplePaginate($data->perPage, ['*'], 'page', $data->page)
            ->withQueryString();

        Cache::put($key, [
            'items' => array_map(
                static fn (mixed $item): mixed => self::normalizeCacheItem($item),
                $paginator->items()
            ),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ], self::CACHE_TTL_SECONDS);

        return $paginator;
    }

    private static function normalizeCacheItem(mixed $item): mixed
    {
        if ($item instanceof \Illuminate\Database\Eloquent\Model) {
            return $item->toArray();
        }

        if (is_array($item)) {
            return array_map(static fn (mixed $value): mixed => self::normalizeCacheItem($value), $item);
        }

        if (is_object($item)) {
            return json_decode(json_encode($item, JSON_THROW_ON_ERROR), true);
        }

        return $item;
    }
}
