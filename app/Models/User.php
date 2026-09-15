<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\TravelerProfile;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'phone', 'password', 'status'];

    /**
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Roles and permissions are evaluated against the same JWT API guard that
     * protects the client and admin applications.
     */
    protected string $guard_name = 'api';

    /**
     * New users default to active. Declared here, not just as the `status`
     * column's DB-level default, because Eloquent never syncs a DB default
     * back into the in-memory model after INSERT — without this, `$user->status`
     * reads as null immediately after `create()` until the model is refreshed.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => UserStatus::Active->value,
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    /**
     * @return HasOne<TravelerProfile, $this>
     */
    public function travelerProfile(): HasOne
    {
        return $this->hasOne(TravelerProfile::class);
    }
}
