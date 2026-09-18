<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Category;
use App\Support\Contracts\Action;
use Illuminate\Support\Str;

class UpdateCategory implements Action
{
    /**
     * @param  array{name?: string, slug?: string, applies_to?: string}  $data
     */
    public function handle(Category $category, array $data): Category
    {
        if (isset($data['name']) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return $category;
    }
}
