<?php

namespace App\Domains\Experiences\Models;

use App\Domains\Experiences\Enums\PolicyType;
use Database\Factories\PolicyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Policy extends Model
{
    /** @use HasFactory<PolicyFactory> */
    use HasFactory;

    /**
     * @return Factory<Policy>
     */
    protected static function newFactory(): Factory
    {
        return PolicyFactory::new();
    }

    /** @var list<string> */
    protected $fillable = [
        'policy_type',
        'title',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'policy_type' => PolicyType::class,
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function policyable(): MorphTo
    {
        return $this->morphTo();
    }
}
