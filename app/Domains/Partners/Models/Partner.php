<?php

namespace App\Domains\Partners\Models;

use App\Domains\Partners\Enums\PartnerType;
use Database\Factories\PartnerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    /** @use HasFactory<PartnerFactory> */
    use HasFactory;

    /**
     * @return Factory<Partner>
     */
    protected static function newFactory(): Factory
    {
        return PartnerFactory::new();
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'partner_type',
    ];

    protected function casts(): array
    {
        return [
            'partner_type' => PartnerType::class,
        ];
    }

    /**
     * @return HasMany<PartnerProfile, $this>
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(PartnerProfile::class);
    }
}
