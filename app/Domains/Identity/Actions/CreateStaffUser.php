<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Models\StaffProfile;
use App\Models\User;
use App\Support\Contracts\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Admin-only entry point for provisioning a staff account — creates the User,
 * assigns its staff role, and creates the matching StaffProfile together, so a
 * staff account never exists without one.
 */
class CreateStaffUser implements Action
{
    /**
     * @param  array{name: string, email: string, password: string, role: string, department_id?: int|null, employee_code?: string|null, hire_date?: string|null}  $data
     */
    public function handle(array $data): StaffProfile
    {
        return DB::transaction(function () use ($data): StaffProfile {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole($data['role']);

            $profile = $user->staffProfile()->create([
                'department_id' => $data['department_id'] ?? null,
                'employee_code' => $data['employee_code'] ?? null,
                'hire_date' => $data['hire_date'] ?? null,
            ]);

            return $profile->setRelation('user', $user);
        });
    }
}
