<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Models\TravelerProfile;
use App\Support\Contracts\Action;

class UpdateTravelerProfile implements Action
{
    /**
     * @param  array{date_of_birth?: string|null, nationality?: string|null, passport_number?: string|null, emergency_contact_name?: string|null, emergency_contact_phone?: string|null, preferences?: array<string, mixed>|null}  $data
     */
    public function handle(TravelerProfile $profile, array $data): TravelerProfile
    {
        $profile->update($data);

        return $profile;
    }
}
