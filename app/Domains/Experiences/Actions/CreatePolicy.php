<?php

namespace App\Domains\Experiences\Actions;

use App\Domains\Experiences\Models\Experience;
use App\Domains\Experiences\Models\Policy;
use App\Support\Contracts\Action;

class CreatePolicy implements Action
{
    /**
     * @param  array{policy_type: string, title: string, body: string}  $data
     */
    public function handle(Experience $policyable, array $data): Policy
    {
        return $policyable->policies()->create($data);
    }
}
