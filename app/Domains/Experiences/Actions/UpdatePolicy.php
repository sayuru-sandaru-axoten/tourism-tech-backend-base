<?php

namespace App\Domains\Experiences\Actions;

use App\Domains\Experiences\Models\Policy;
use App\Support\Contracts\Action;

class UpdatePolicy implements Action
{
    /**
     * @param  array{policy_type?: string, title?: string, body?: string}  $data
     */
    public function handle(Policy $policy, array $data): Policy
    {
        $policy->update($data);

        return $policy;
    }
}
