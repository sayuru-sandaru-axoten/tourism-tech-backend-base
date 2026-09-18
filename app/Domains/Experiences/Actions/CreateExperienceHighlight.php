<?php

namespace App\Domains\Experiences\Actions;

use App\Domains\Experiences\Models\Experience;
use App\Domains\Experiences\Models\ExperienceHighlight;
use App\Support\Contracts\Action;

class CreateExperienceHighlight implements Action
{
    /**
     * @param  array{title: string, description?: string|null, sort_order?: int}  $data
     */
    public function handle(Experience $experience, array $data): ExperienceHighlight
    {
        return $experience->highlights()->create($data);
    }
}
