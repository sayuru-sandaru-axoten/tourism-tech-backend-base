<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Translation;
use App\Support\Contracts\Action;

class DeleteTranslation implements Action
{
    public function handle(Translation $translation): void
    {
        $translation->delete();
    }
}
