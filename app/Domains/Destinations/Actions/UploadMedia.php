<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Media;
use App\Domains\Destinations\Models\Place;
use App\Domains\Experiences\Models\Experience;
use App\Support\Contracts\Action;
use Illuminate\Http\UploadedFile;

class UploadMedia implements Action
{
    /**
     * @param  array{file: UploadedFile, alt_text?: string|null, sort_order?: int}  $data
     */
    public function handle(Destination|Place|Experience $mediable, array $data): Media
    {
        $file = $data['file'];
        $path = $file->store($mediable->getMorphClass().'-media', 'public');

        return $mediable->media()->create([
            'disk' => 'public',
            'path' => $path,
            'media_type' => str($file->getMimeType() ?? '')->startsWith('video/') ? 'video' : 'image',
            'alt_text' => $data['alt_text'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }
}
