<?php

namespace App\Domains\Destinations\Models;

use App\Domains\Destinations\Enums\DestinationStatus;
use Database\Factories\DestinationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Destination extends Model
{
    /** @use HasFactory<DestinationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return Factory<Destination>
     */
    protected static function newFactory(): Factory
    {
        return DestinationFactory::new();
    }

    /** @var list<string> */
    protected $fillable = [
        'region_id',
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'latitude',
        'longitude',
        'rating_avg',
        'rating_count',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'rating_avg' => 'decimal:2',
            'rating_count' => 'integer',
            'status' => DestinationStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<Place, $this>
     */
    public function places(): HasMany
    {
        return $this->hasMany(Place::class);
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
     * @param  Builder<Destination>  $query
     * @return Builder<Destination>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', DestinationStatus::Published);
    }
}
