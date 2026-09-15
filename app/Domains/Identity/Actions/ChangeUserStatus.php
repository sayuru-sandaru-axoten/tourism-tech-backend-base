<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Enums\UserStatus;
use App\Models\User;
use App\Support\Contracts\Action;

class ChangeUserStatus implements Action
{
    public function handle(User $user, UserStatus $status): User
    {
        $user->update(['status' => $status]);

        return $user;
    }
}
