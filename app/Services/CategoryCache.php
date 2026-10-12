<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;


class CategoryCache
{
    private const KEY = 'categories:ids';
    private const TTL = 3600;

    /** @return list<int> */
    public function ids(): array
    {
        return Cache::remember(self::KEY, self::TTL, fn () => Category::query()->pluck('id')->all());
    }

    public function flush(): void
    {
        Cache::forget(self::KEY);
    }
}
