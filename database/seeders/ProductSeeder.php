<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private const TOTAL = 1_000_000;
    private const BATCH = 5_000;

    private const WORDS = [
        'laptop', 'phone', 'camera', 'headphones', 'keyboard', 'monitor', 'mouse', 'speaker',
        'tablet', 'watch', 'printer', 'router', 'charger', 'cable', 'battery', 'pro', 'max',
        'ultra', 'mini', 'wireless', 'gaming', 'portable', 'smart', 'premium', 'classic',
        'لابتوب', 'موبايل', 'سماعة', 'شاشة', 'كاميرا', 'طابعة',
    ];

    public function run(): void
    {
        DB::disableQueryLog();

        $categoryIds = $this->seedCategories();
        $now = now()->toDateTimeString();
        $nowTs = time();
        $maxWord = count(self::WORDS) - 1;
        $maxCat = count($categoryIds) - 1;

        for ($i = 0; $i < self::TOTAL; $i += self::BATCH) {
            $rows = [];

            for ($j = 0; $j < self::BATCH; $j++) {
                $name = implode(' ', [
                    self::WORDS[random_int(0, $maxWord)],
                    self::WORDS[random_int(0, $maxWord)],
                    self::WORDS[random_int(0, $maxWord)],
                ]);

                $description = implode(' ', array_map(
                    fn () => self::WORDS[random_int(0, $maxWord)],
                    range(1, 10)
                ));

                $created = date('Y-m-d H:i:s', $nowTs - random_int(0, 2 * 365 * 86400));

                $rows[] = [
                    'category_id' => $categoryIds[random_int(0, $maxCat)],
                    'name' => ucfirst($name),
                    'description' => $description,
                    'price' => random_int(500, 5_000_000) / 100,
                    'is_available' => random_int(1, 100) <= 80,
                    // 'created_at' => $created,
                    // 'updated_at' => $now,
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                    'updated_at' => Carbon::now(),
                ];
            }

            DB::table('products')->insert($rows);
            $this->command?->info('Inserted '.($i + self::BATCH).' products');
        }
    }

    /** @return list<int> */
    private function seedCategories(): array
    {
        foreach (range(1, 20) as $n) {
            Category::firstOrCreate(['slug' => "category-{$n}"], ['name' => "Category {$n}"]);
        }

        return Category::pluck('id')->all();
    }
}
