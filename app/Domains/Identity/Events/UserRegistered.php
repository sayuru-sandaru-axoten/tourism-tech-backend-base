<?php

namespace App\Domains\Identity\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Raised after a new user has been created and assigned its default role.
 * Not on the "critical delivery" list in ARCHITECTURE.md §4 (Identity has no
 * dependent domain yet), so a plain synchronous dispatch is enough for now —
 * revisit if a future listener (e.g. Loyalty's welcome bonus) needs the
 * transactional-outbox-via-database-queue treatment.
 */
class UserRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user,
    ) {}
}
