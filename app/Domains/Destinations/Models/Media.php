<?php

namespace App\Domains\Destinations\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    /**
     * @return Factory<Media>
     */
    protected static function newFactory(): Factory
    {
        return MediaFactory::new();
    }

    /** @var list<string> */
    protected $fillable = [
        'disk',
        'path',
        'media_type',
        'alt_text',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn (): string => Storage::disk($this->disk)->url($this->path),
        );
    }
}
