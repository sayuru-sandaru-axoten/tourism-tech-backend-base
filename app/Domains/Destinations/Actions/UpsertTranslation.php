<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Place;
use App\Domains\Destinations\Models\Translation;
use App\Domains\Experiences\Models\Experience;
use App\Support\Contracts\Action;

class UpsertTranslation implements Action
{
    /**
     * @param  array{locale: string, field_key: string, value: string}  $data
     */
    public function handle(Destination|Place|Experience $translatable, array $data): Translation
    {
        return $translatable->translations()->updateOrCreate(
            ['locale' => $data['locale'], 'field_key' => $data['field_key']],
            ['value' => $data['value']],
        );
    }
}
