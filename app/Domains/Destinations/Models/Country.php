<?php

namespace App\Domains\Destinations\Models;

use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    /**
     * @return Factory<Country>
     */
    protected static function newFactory(): Factory
    {
        return CountryFactory::new();
    }

    /** @var list<string> */
    protected $fillable = [
        'name',
        'iso_code',
    ];

    /**
     * @return HasMany<Region, $this>
     */
    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }
}
