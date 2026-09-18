<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Media;
use App\Support\Contracts\Action;

class ReorderMedia implements Action
{
    /**
     * Updates `sort_order` for the given media ids, but only within a single
     * mediable — items must all share the same `mediable_type`/`mediable_id`
     * as the first item, otherwise the reorder is rejected wholesale.
     *
     * @param  list<array{id: int, sort_order: int}>  $items
     */
    public function handle(array $items): void
    {
        $ids = array_column($items, 'id');

        $media = Media::query()->whereIn('id', $ids)->get(['id', 'mediable_type', 'mediable_id']);

        if ($media->isEmpty()) {
            return;
        }

        $first = $media->first();
        $sameMediable = $media->every(
            fn (Media $m): bool => $m->mediable_type === $first->mediable_type && $m->mediable_id === $first->mediable_id,
        );

        if (! $sameMediable) {
            return;
        }

        $sortOrderById = array_column($items, 'sort_order', 'id');

        foreach ($media as $item) {
            Media::query()->whereKey($item->id)->update(['sort_order' => $sortOrderById[$item->id]]);
        }
    }
}
