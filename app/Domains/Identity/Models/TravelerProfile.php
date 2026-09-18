<?php

namespace App\Domains\Identity\Models;

use App\Models\User;
use Database\Factories\TravelerProfileFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelerProfile extends Model
{
    /** @use HasFactory<TravelerProfileFactory> */
    use HasFactory;

    /**
     * Explicit, not convention-resolved — this model lives outside `App\Models\`,
     * which Laravel's default factory-name resolver assumes.
     *
     * @return Factory<TravelerProfile>
     */
    protected static function newFactory(): Factory
    {
        return TravelerProfileFactory::new();
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'date_of_birth',
        'nationality',
        'passport_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'preferences',
    ];

    /**
     * `loyalty_tier`/`loyalty_points` are deliberately not fillable — they're
     * owned by the future Loyalty domain, not settable by a traveler-facing
     * profile update.
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'passport_number' => 'encrypted',
            'preferences' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
