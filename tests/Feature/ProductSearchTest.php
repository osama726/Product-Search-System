<?php

namespace Tests\Feature;

use App\Actions\Products\SearchProductsAction;
use App\DTOs\ProductSearchData;
use App\Enums\ProductSortField;
use App\Enums\SortDirection;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use DatabaseTruncation;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
    }

    private function product(array $attrs = []): Product
    {
        return Product::create($attrs + [
            'category_id' => $this->category->id,
            'name' => 'Generic Item',
            'description' => 'Some description',
            'price' => 100,
            'is_available' => true,
        ]);
    }

    public function test_it_searches_by_name_and_description(): void
    {
        $this->product(['name' => 'Gaming Laptop']);
        $this->product(['name' => 'Desk', 'description' => 'Fits any laptop']);
        $this->product(['name' => 'Banana']);

        $this->getJson('/api/products/search?q=laptop')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_empty_search_returns_listing(): void
    {
        $this->product();
        $this->product();

        $this->getJson('/api/products/search?q=')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/products/search?q=%20%20')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_special_characters_do_not_break_search(): void
    {
        $this->product(['name' => 'Gaming Laptop']);

        $this->getJson('/api/products/search?q='.urlencode('+laptop* -"(@~'))->assertOk();
    }

    public function test_short_search_matches_name_and_description_fallback(): void
    {
        $this->product(['name' => 'Desk', 'description' => 'TV stand for living room']);
        $this->product(['name' => 'Travel Bag']);

        $this->getJson('/api/products/search?q=tv')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Desk');
    }

    public function test_price_range_category_and_availability_filters(): void
    {
        $other = Category::create(['name' => 'Books', 'slug' => 'books']);

        $this->product(['name' => 'A', 'price' => 50]);
        $this->product(['name' => 'B', 'price' => 150]);
        $this->product(['name' => 'C', 'price' => 150, 'is_available' => false]);
        $this->product(['name' => 'D', 'price' => 150, 'category_id' => $other->id]);

        $this->getJson("/api/products/search?min_price=100&max_price=200&only_available=true&category_id={$this->category->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'B');
    }

    public function test_sorting_by_price(): void
    {
        $this->product(['name' => 'Mid', 'price' => 200]);
        $this->product(['name' => 'Low', 'price' => 100]);
        $this->product(['name' => 'High', 'price' => 300]);

        $this->getJson('/api/products/search?sort=price&direction=asc')
            ->assertJsonPath('data.0.name', 'Low')
            ->assertJsonPath('data.2.name', 'High');
    }

    public function test_per_page_is_capped_at_50(): void
    {
        $this->getJson('/api/products/search?per_page=51')
            ->assertStatus(422)
            ->assertJsonValidationErrors('per_page');
    }

    public function test_invalid_inputs_are_rejected(): void
    {
        $this->getJson('/api/products/search?min_price=abc')->assertStatus(422);
        $this->getJson('/api/products/search?min_price=500&max_price=100')->assertStatus(422);
        $this->getJson('/api/products/search?sort=hack')->assertStatus(422);
        $this->getJson('/api/products/search?category_id=9999')->assertStatus(422);
        $this->getJson('/api/products/search?only_available=maybe')->assertStatus(422);
    }

    public function test_pagination_respects_per_page(): void
    {
        foreach (range(1, 5) as $i) {
            $this->product(['name' => "Item {$i}"]);
        }

        $this->getJson('/api/products/search?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.pagination.per_page', 2)
            ->assertJsonPath('meta.pagination.has_more', true);
    }

    public function test_success_response_has_unified_envelope(): void
    {
        $this->product();

        $this->getJson('/api/products/search')
            ->assertOk()
            ->assertJsonStructure([
                'success', 'message',
                'data' => [['id', 'name', 'description', 'price', 'is_available', 'category' => ['id', 'name', 'slug'], 'created_at']],
                'meta' => ['pagination' => ['current_page', 'per_page', 'count', 'has_more', 'next_page_url', 'prev_page_url']],
            ])
            ->assertJsonPath('success', true);
    }

    public function test_validation_and_not_found_errors_use_same_envelope(): void
    {
        $this->getJson('/api/products/search?per_page=51')
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message', 'data', 'errors' => ['per_page']]);

        $this->getJson('/api/nope')
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_repeated_searches_are_cached_for_speed(): void
    {
        $this->product(['name' => 'Gaming Laptop']);
        $this->product(['name' => 'Laptop Stand']);

        $requestData = new ProductSearchData(
            query: 'laptop',
            minPrice: null,
            maxPrice: null,
            categoryId: null,
            onlyAvailable: false,
            sort: ProductSortField::Relevance,
            direction: SortDirection::Desc,
            perPage: 10,
            page: 1,
        );

        $this->assertNull(Cache::get(SearchProductsAction::cacheKey($requestData)));

        $this->getJson('/api/products/search?q=laptop&per_page=10');

        $this->assertNotNull(Cache::get(SearchProductsAction::cacheKey($requestData)));
    }
}
