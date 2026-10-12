<?php

namespace App\Models;

use App\Services\CategoryCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug'])]
class Category extends Model
{
    protected static function booted(): void
    {
        $flush = fn () => app(CategoryCache::class)->flush();

        static::saved($flush);
        static::deleted($flush);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
