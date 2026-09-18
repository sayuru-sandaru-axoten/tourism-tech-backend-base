<?php

namespace App\Domains\Experiences\Models;

use App\Domains\Destinations\Models\Category;
use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Media;
use App\Domains\Destinations\Models\Translation;
use App\Domains\Experiences\Enums\ExperienceStatus;
use App\Domains\Experiences\Enums\ExperienceType;
use App\Domains\Partners\Models\Partner;
use Database\Factories\ExperienceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Experience extends Model
{
    /** @use HasFactory<ExperienceFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return Factory<Experience>
     */
    protected static function newFactory(): Factory
    {
        return ExperienceFactory::new();
    }

    /** @var list<string> */
    protected $fillable = [
        'destination_id',
        'partner_id',
        'experience_type',
        'title',
        'slug',
        'short_description',
        'description',
        'duration_days',
        'duration_nights',
        'min_group_size',
        'max_group_size',
        'base_price',
        'currency',
        'rating_avg',
        'rating_count',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'experience_type' => ExperienceType::class,
            'duration_days' => 'integer',
            'duration_nights' => 'integer',
            'min_group_size' => 'integer',
            'max_group_size' => 'integer',
            'base_price' => 'decimal:2',
            'rating_avg' => 'decimal:2',
            'rating_count' => 'integer',
            'status' => ExperienceStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Destination, $this>
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * @return BelongsTo<Partner, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'experience_category');
    }

    /**
     * @return HasMany<ExperienceHighlight, $this>
     */
    public function highlights(): HasMany
    {
        return $this->hasMany(ExperienceHighlight::class);
    }

    /**
     * @return MorphMany<Policy, $this>
     */
    public function policies(): MorphMany
    {
        return $this->morphMany(Policy::class, 'policyable');
    }

    /**
     * @return MorphMany<Media, $this>
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * @return MorphMany<Translation, $this>
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * @param  Builder<Experience>  $query
     * @return Builder<Experience>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ExperienceStatus::Published);
    }
}
