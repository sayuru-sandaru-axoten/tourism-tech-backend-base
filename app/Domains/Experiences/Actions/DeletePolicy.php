<?php

namespace App\Domains\Experiences\Actions;

use App\Domains\Experiences\Models\Policy;
use App\Support\Contracts\Action;

class DeletePolicy implements Action
{
    public function handle(Policy $policy): void
    {
        $policy->delete();
    }
}
