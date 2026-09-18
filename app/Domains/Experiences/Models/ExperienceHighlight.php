<?php

namespace App\Domains\Experiences\Models;

use Database\Factories\ExperienceHighlightFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienceHighlight extends Model
{
    /** @use HasFactory<ExperienceHighlightFactory> */
    use HasFactory;

    /**
     * @return Factory<ExperienceHighlight>
     */
    protected static function newFactory(): Factory
    {
        return ExperienceHighlightFactory::new();
    }

    /** @var list<string> */
    protected $fillable = [
        'experience_id',
        'title',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Experience, $this>
     */
    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }
}
