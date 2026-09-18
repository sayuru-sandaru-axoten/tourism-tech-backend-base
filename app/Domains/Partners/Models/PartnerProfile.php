<?php

namespace App\Domains\Partners\Models;

use App\Models\User;
use Database\Factories\PartnerProfileFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerProfile extends Model
{
    /** @use HasFactory<PartnerProfileFactory> */
    use HasFactory;

    /**
     * @return Factory<PartnerProfile>
     */
    protected static function newFactory(): Factory
    {
        return PartnerProfileFactory::new();
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'job_title',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Partner, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
