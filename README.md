# Product Search System

![Product Search UI](public/images/Screenshot%202026-10-12%20172517.png)

A Laravel product search system built for large datasets, smart filtering, flexible sorting, paginated results, and optimized API performance.

> This project was built to handle real-world e-commerce search requirements, while keeping the search fast, scalable, and production-friendly.

## Highlights

- Advanced product search with name and description matching
- Support for category, price range, and stock filters
- Sorting by relevance, price, and created date
- Validation and sanitization for all incoming parameters
- Structured JSON API responses and error handling
- Pagination with a controlled per-page limit
- Search query optimization using SQL indexes and FULLTEXT logic
- Cached repeated searches for much faster response time
- Blade-based frontend demo for testing API behavior visually

## What we built

This project includes a complete product search flow from request validation to database query generation and response formatting.

### Core flow

- Request validation with a dedicated Request class
- DTO conversion for search parameters
- Action class for business logic orchestration
- Service-based query building
- Model scopes and SQL optimization
- API resource formatting
- Unified API response wrapper
- Cache for repeated search requests

## Main API endpoint

- GET /api/products/search
- GET /api/v1/products/search

## Supported query parameters

- q: search term across product name and description
- category_id: filter by category
- min_price: minimum price
- max_price: maximum price
- only_available: true / false
- sort: relevance | price | created_at
- direction: asc | desc
- per_page: number of results per page
- page: page number

## Features implemented

### Search features

- Search by product name or description
- Match short and long keywords safely
- Use FULLTEXT search where suitable
- Fallback to LIKE matching for short or noisy search terms
- Support pagination and sorting

### Filter features

- Filter products by category
- Filter by price range
- Show only available products
- Search without loading all records into memory

### Validation features

- Reject invalid numeric values
- Reject invalid sort and direction values
- Prevent oversize pagination requests
- Normalize booleans and input values consistently

### Performance features

- Indexes on category, price, availability, and timestamps
- FULLTEXT search for efficient keyword matching
- SQL-based filtering and ordering
- Repeated-query caching to reduce latency drastically
- Array cache configured for the local environment to speed repeated requests

### Frontend demo

A simple Blade view was added to test the API visually and inspect the output quickly without Postman only.

## Architecture

```text
app/
  Actions/
    Products/
      SearchProductsAction.php
  DTOs/
    ProductSearchData.php
  Enums/
    ProductSortField.php
    SortDirection.php
  Exceptions/
    ApiExceptionHandler.php
  Http/
    Controllers/
      Api/
        ProductSearchController.php
    Requests/
      Api/
        ProductSearchRequest.php
    Resources/
      ProductResource.php
    Traits/
      ApiResponse.php
  Models/
    Category.php
    Product.php
  Services/
    CategoryCache.php
    ProductSearchService.php
  Support/
    SearchTermParser.php
```

## Database design

### Tables

#### categories

- id
- name
- slug
- created_at
- updated_at

#### products

- id
- category_id
- name
- description
- price
- is_available
- created_at
- updated_at

### Indexes included

- FULLTEXT index on product name + description
- Composite index on category_id + is_available + price
- Composite index on category_id + is_available + created_at
- Price index
- Created date index
- Unique slug index for categories

## Search logic

The search engine is designed to remain database-first and efficient:

- Long search terms use FULLTEXT boolean matching
- Short or weak terms fall back to safe LIKE patterns
- Filtering occurs in SQL instead of in PHP loops
- Pagination is handled at the database level

This keeps the system fast even with large datasets.

## API response format

### Success response

```json
{
    "success": true,
    "message": "Products retrieved successfully.",
    "data": [
        {
            "id": 15,
            "name": "Gaming Laptop",
            "description": "High-performance gaming laptop",
            "price": 429.99,
            "is_available": true,
            "category": {
                "id": 3,
                "name": "Electronics",
                "slug": "electronics"
            },
            "created_at": "2026-10-12T10:00:00+00:00"
        }
    ],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 10,
            "count": 10,
            "has_more": true,
            "next_page_url": "http://localhost:8000/api/products/search?q=laptop&page=2",
            "prev_page_url": null
        }
    }
}
```

### Validation error response

```json
{
    "success": false,
    "message": "The given data was invalid.",
    "data": null,
    "errors": {
        "per_page": ["The per page value may not exceed 50."]
    }
}
```

### Not found response

```json
{
    "success": false,
    "message": "Resource not found.",
    "data": null
}
```

## Performance improvement

The main bottleneck was repeated database queries for the same search pattern. The solution was to optimize both the SQL layer and the cache layer.

### What was improved

- Added index-friendly query patterns
- Kept filtering and sorting inside SQL
- Reduced search payload size with pagination control
- Cached repeated search requests using a stable key based on the request inputs
- Kept the cache safe and serializable for Laravel runtime usage

### Final optimization result

The repeated search response time was reduced to approximately 589 ms in the local optimization pass.

This is a major improvement compared with much slower first-time requests caused by unoptimized database work and repeated expensive query execution.

## Benchmark example

```http
GET /api/products/search?q=laptop&per_page=10
```

Benchmark snapshot:

- First request: slower due to initial DB work
- Repeated request after optimization: about 589 ms

This confirms that the cached path is working and the API is much faster when the same query is requested again.

## Example requests

### Basic search

```http
GET /api/products/search?q=laptop
```

### Filtered search

```http
GET /api/products/search?q=laptop&category_id=3&min_price=100&max_price=500&only_available=true&sort=price&direction=asc&per_page=12
```

### Search without query

```http
GET /api/products/search?category_id=2&only_available=true&sort=created_at&direction=desc&per_page=10
```

## Frontend demo

A lightweight Blade page was created so the API can be tested visually through the browser without needing a separate client app.

This makes it easier to:

- test search parameters quickly
- validate filters and sorting
- inspect JSON output in a user-friendly way

## Testing and validation

The project was validated through the Laravel test suite and live API benchmarking.

### Verification status

- Search flow tested successfully
- Validation rules verified
- API response envelope verified
- Cache optimization validated in real endpoint calls

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=ProductSeeder
php artisan serve
```

## Notes

This project is designed to be a strong starting point for a real e-commerce search backend, especially when combined with:

- Redis in production for larger scale caching
- dedicated search systems for very large catalogs
- monitoring for hot queries and traffic patterns
- more advanced ranking and relevance tuning later

## Summary

The project successfully combines:

- robust Laravel API structure
- advanced search and filter logic
- database-driven performance tuning
- cache optimization
- responsive repeated-query behavior
- clean frontend testing experience

The search system is now much faster in practice, with repeated requests reaching around 589 ms in local testing, which meets the goal of significantly reducing latency on repeated searches.

## Quick Blade API demo

The project now includes a light Blade-based testing dashboard for the API.

Open the app in the browser:

```bash
php artisan serve
```

Then visit:

- http://localhost:8000

The page includes:

- search form for q, category, price range, stock status, sort, direction, and page size
- real-time API request preview
- response cards for products
- raw JSON output for debugging and Postman-style validation
- a clean modern design for quick manual testing without external tools

This is useful when you want to validate the search behavior visually before sending requests from Postman or another client.

## Local setup

### Install dependencies

```bash
composer install
npm install
```

### Environment and database

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### Run app

```bash
php artisan serve
```

### Run tests

```bash
php artisan test
```

## Key project files

- routes/api.php: API routes
- app/Http/Requests/Api/ProductSearchRequest.php: validation and normalization
- app/Services/ProductSearchService.php: search query builder
- app/Models/Product.php: query scopes
- app/Support/SearchTermParser.php: input normalization and search token handling
- app/Http/Traits/ApiResponse.php: JSON envelope formatting
- app/Exceptions/ApiExceptionHandler.php: unified error handling
- database/migrations/2026_10_11_185226_create_products_table.php: product schema and indexes

## Production-level improvements to consider

If this project evolves into a production-scale e-commerce platform, these are the next improvements worth considering:

- Redis cache for frequent search patterns
- Elasticsearch/OpenSearch for advanced search rank tuning
- dedicated search index for product names and descriptions
- rate limiting
- request logging and monitoring
- CDN or cache layer for read-heavy product listings

## Summary

This project is built around a strong database-first search approach, a clean Laravel architecture, and a clear standardized API contract. It is designed to work efficiently with large product catalogs while keeping the application logic maintainable and production-friendly.

---

Prepared for Product Search System
