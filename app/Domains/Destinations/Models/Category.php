<?php

namespace App\Domains\Destinations\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * @return Factory<Category>
     */
    protected static function newFactory(): Factory
    {
        return CategoryFactory::new();
    }

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'applies_to',
    ];

    /**
     * @return HasMany<Destination, $this>
     */
    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }

    /**
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeAppliesTo(Builder $query, string $type): Builder
    {
        return $query->where('applies_to', $type);
    }
}
