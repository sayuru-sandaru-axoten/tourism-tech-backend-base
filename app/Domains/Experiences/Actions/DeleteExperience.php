<?php

namespace App\Domains\Experiences\Actions;

use App\Domains\Experiences\Models\Experience;
use App\Support\Contracts\Action;

class DeleteExperience implements Action
{
    /**
     * Soft-deletes the experience. Its highlights, policies, media, and
     * translations are left intact (no FK cascade fires on an Eloquent soft
     * delete) — they become inaccessible via the experience but are not
     * purged.
     */
    public function handle(Experience $experience): void
    {
        $experience->delete();
    }
}
