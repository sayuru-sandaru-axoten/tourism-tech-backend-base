<?php

namespace App\Domains\Destinations\Models;

use Database\Factories\RegionFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    /** @use HasFactory<RegionFactory> */
    use HasFactory;

    /**
     * @return Factory<Region>
     */
    protected static function newFactory(): Factory
    {
        return RegionFactory::new();
    }

    /** @var list<string> */
    protected $fillable = [
        'country_id',
        'name',
        'slug',
    ];

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return HasMany<Destination, $this>
     */
    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }
}
