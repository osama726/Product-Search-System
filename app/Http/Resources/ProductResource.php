<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'is_available' => (bool) $this->is_available,
            'category' => [
                'id' => (int) $this->category_id,
                'name' => $this->category_name,
                'slug' => $this->category_slug,
            ],
            'created_at' => Carbon::parse($this->created_at)->toIso8601String(),
        ];
    }
}
