<?php

namespace App\Domains\Experiences\Actions;

use App\Domains\Experiences\Models\ExperienceHighlight;
use App\Support\Contracts\Action;

class UpdateExperienceHighlight implements Action
{
    /**
     * @param  array{title?: string, description?: string|null, sort_order?: int}  $data
     */
    public function handle(ExperienceHighlight $highlight, array $data): ExperienceHighlight
    {
        $highlight->update($data);

        return $highlight;
    }
}
