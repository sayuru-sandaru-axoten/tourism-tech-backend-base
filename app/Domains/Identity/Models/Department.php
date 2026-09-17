<?php

namespace App\Domains\Identity\Models;

use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory;

    /**
     * Explicit, not convention-resolved — this model lives outside `App\Models\`,
     * which Laravel's default factory-name resolver assumes.
     *
     * @return Factory<Department>
     */
    protected static function newFactory(): Factory
    {
        return DepartmentFactory::new();
    }

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<StaffProfile, $this>
     */
    public function staffProfiles(): HasMany
    {
        return $this->hasMany(StaffProfile::class);
    }
}
