<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Category;
use App\Support\Contracts\Action;

class DeleteCategory implements Action
{
    public function handle(Category $category): void
    {
        $category->delete();
    }
}
