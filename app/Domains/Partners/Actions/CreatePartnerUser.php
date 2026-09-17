<?php

namespace App\Domains\Partners\Actions;

use App\Domains\Partners\Models\Partner;
use App\Domains\Partners\Models\PartnerProfile;
use App\Models\User;
use App\Support\Contracts\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Admin-only entry point for provisioning a partner user account — creates
 * (or reuses) the Partner organisation, the User, its `partner-user` role,
 * and the matching PartnerProfile together.
 */
class CreatePartnerUser implements Action
{
    /**
     * @param  array{name: string, email: string, password: string, job_title?: string|null, partner_id?: int|null, partner_name?: string|null, partner_type?: string|null}  $data
     */
    public function handle(array $data): PartnerProfile
    {
        return DB::transaction(function () use ($data): PartnerProfile {
            $partner = isset($data['partner_id'])
                ? Partner::query()->findOrFail($data['partner_id'])
                : Partner::query()->create([
                    'name' => $data['partner_name'],
                    'partner_type' => $data['partner_type'],
                ]);

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('partner-user');

            // `partner_id`/`user_id` aren't mass-assignable on PartnerProfile (only
            // `job_title` is) — associate() sets the foreign keys directly instead.
            $profile = new PartnerProfile(['job_title' => $data['job_title'] ?? null]);
            $profile->user()->associate($user);
            $profile->partner()->associate($partner);
            $profile->save();

            return $profile;
        });
    }
}
