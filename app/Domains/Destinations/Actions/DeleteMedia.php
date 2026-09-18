<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Media;
use App\Support\Contracts\Action;
use Illuminate\Support\Facades\Storage;

class DeleteMedia implements Action
{
    public function handle(Media $media): void
    {
        Storage::disk($media->disk)->delete($media->path);

        $media->delete();
    }
}
