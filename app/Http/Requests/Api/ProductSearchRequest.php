<?php

namespace App\Http\Requests\Api;

use App\DTOs\ProductSearchData;
use App\Enums\ProductSortField;
use App\Enums\SortDirection;
use App\Services\CategoryCache;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductSearchRequest extends FormRequest
{
    public const MAX_PER_PAGE = 50;
    public const DEFAULT_PER_PAGE = 15;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $q = $this->input('q');

        $this->merge([
            // Normalize the 'q' parameter: if it's a non-empty string, trim it; otherwise, set it to null
            'q' => is_string($q) && trim($q) !== '' ? trim($q) : null,
            // Normalize boolean values for 'only_available' parameter
            'only_available' => $this->normalizeBoolean($this->input('only_available')),
        ]);
    }

    public function rules(): array
    {
        return [
            'q' =>                  ['nullable', 'string', 'max:100'],
            'min_price' =>          ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'max_price' =>          ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:min_price'],
            'category_id' =>        ['nullable', 'integer', 'min:1', Rule::in(app(CategoryCache::class)->ids())],
            'only_available' =>     ['nullable', 'boolean'],
            'sort' =>               ['nullable', Rule::enum(ProductSortField::class)],
            'direction' =>          ['nullable', Rule::enum(SortDirection::class)],
            'per_page' =>           ['nullable', 'integer', 'min:1', 'max:'.self::MAX_PER_PAGE],
            'page' =>               ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'max_price.gte' => 'The max price must be greater than or equal to the min price.',
            'per_page.max' => 'The per page value may not exceed '.self::MAX_PER_PAGE.'.',
        ];
    }

    public function toData(): ProductSearchData
    {
        $v = $this->validated();
        $query = $v['q'] ?? null;

        // If the user requested sorting by relevance but didn't provide a search query, we can't sort by relevance, so we default to sorting by creation date.
        $sort = isset($v['sort'])
            ? ProductSortField::from($v['sort'])
            : ($query !== null ? ProductSortField::Relevance : ProductSortField::CreatedAt);

        // If the user requested sorting by relevance but didn't provide a search query, we can't sort by relevance, so we default to sorting by creation date.
        if ($sort === ProductSortField::Relevance && $query === null) {
            $sort = ProductSortField::CreatedAt;
        }

        $defaultDirection = $sort === ProductSortField::Price ? SortDirection::Asc : SortDirection::Desc;

        return new ProductSearchData(
            query: $query,
            minPrice: isset($v['min_price']) ? (string) $v['min_price'] : null,
            maxPrice: isset($v['max_price']) ? (string) $v['max_price'] : null,
            categoryId: isset($v['category_id']) ? (int) $v['category_id'] : null,
            onlyAvailable: (bool) ($v['only_available'] ?? false),
            sort: $sort,
            direction: isset($v['direction']) ? SortDirection::from($v['direction']) : $defaultDirection,
            perPage: (int) ($v['per_page'] ?? self::DEFAULT_PER_PAGE),
        );
    }

    private function normalizeBoolean(mixed $value): mixed
    {
        return match (true) {
            $value === 'true' => true,
            $value === 'false' => false,
            default => $value,
        };
    }
}
