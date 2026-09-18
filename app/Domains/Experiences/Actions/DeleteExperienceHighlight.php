<?php

namespace App\Domains\Experiences\Actions;

use App\Domains\Experiences\Models\ExperienceHighlight;
use App\Support\Contracts\Action;

class DeleteExperienceHighlight implements Action
{
    public function handle(ExperienceHighlight $highlight): void
    {
        $highlight->delete();
    }
}
