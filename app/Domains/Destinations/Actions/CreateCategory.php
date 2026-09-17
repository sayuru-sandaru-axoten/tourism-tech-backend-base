<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Category;
use App\Support\Contracts\Action;
use Illuminate\Support\Str;

class CreateCategory implements Action
{
    /**
     * @param  array{name: string, slug?: string, applies_to: string}  $data
     */
    public function handle(array $data): Category
    {
        return Category::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'applies_to' => $data['applies_to'],
        ]);
    }
}
