<?php

namespace App\Domains\Identity\Models;

use App\Models\User;
use Database\Factories\StaffProfileFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class StaffProfile extends Model
{
    /** @use HasFactory<StaffProfileFactory> */
    use HasFactory;

    /**
     * Explicit, not convention-resolved — this model lives outside `App\Models\`,
     * which Laravel's default factory-name resolver assumes.
     *
     * @return Factory<StaffProfile>
     */
    protected static function newFactory(): Factory
    {
        return StaffProfileFactory::new();
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'department_id',
        'employee_code',
        'hire_date',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
