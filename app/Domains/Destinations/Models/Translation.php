<?php

namespace App\Domains\Destinations\Models;

use Database\Factories\TranslationFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translation extends Model
{
    /** @use HasFactory<TranslationFactory> */
    use HasFactory;

    /**
     * @return Factory<Translation>
     */
    protected static function newFactory(): Factory
    {
        return TranslationFactory::new();
    }

    /** @var list<string> */
    protected $fillable = [
        'locale',
        'field_key',
        'value',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
