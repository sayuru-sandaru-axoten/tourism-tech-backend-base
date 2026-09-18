<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Destination;
use App\Support\Contracts\Action;

class DeleteDestination implements Action
{
    /**
     * Soft-deletes the destination. Its places, media, and translations are
     * left intact (no FK cascade fires on an Eloquent soft delete) — they
     * become inaccessible via the destination but are not purged.
     */
    public function handle(Destination $destination): void
    {
        $destination->delete();
    }
}
