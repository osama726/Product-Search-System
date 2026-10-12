<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['category_id', 'name', 'description', 'price', 'is_available'])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Scopes
    public function scopeInCategory(Builder $query, int $categoryId): void
    {
        $query->where('products.category_id', $categoryId);
    }

    public function scopeAvailable(Builder $query): void
    {
        $query->where('products.is_available', true);
    }

    public function scopePriceBetween(Builder $query, ?string $min, ?string $max): void
    {
        $query
            ->when($min !== null, fn (Builder $q) => $q->where('products.price', '>=', $min))
            ->when($max !== null, fn (Builder $q) => $q->where('products.price', '<=', $max));
    }

    public function scopeMatchingFullText(Builder $query, string $booleanQuery): void
    {
        $query->whereRaw(
            'MATCH(products.name, products.description) AGAINST (? IN BOOLEAN MODE)',
            [$booleanQuery]
        );
    }

    public function scopeNameStartsWith(Builder $query, string $escapedPrefix): void
    {
        $query->where('products.name', 'like', $escapedPrefix);
    }
}
