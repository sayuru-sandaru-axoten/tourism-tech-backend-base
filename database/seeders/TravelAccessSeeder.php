<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TravelAccessSeeder extends Seeder
{
    private const GUARD = 'api';

    /** @var list<string> */
    private const PERMISSIONS = [
        // Grants access to the staff/admin SPA's session-bootstrap endpoint
        // (ARCHITECTURE.md §5) — every staff role below gets this; `traveler` does not.
        'admin.access',
        'dashboard.view', 'departments.manage', 'destinations.manage', 'experiences.manage',
        'availability.manage', 'pricing.manage', 'itineraries.manage',
        'reservations.view', 'reservations.manage', 'reservations.amend',
        'payments.manage', 'refunds.manage', 'partners.manage', 'content.manage',
        'support.manage', 'reports.view', 'audit.view', 'settings.manage', 'users.manage',
    ];

    /** @var array<string, list<string>> */
    private const ROLE_PERMISSIONS = [
        'traveler' => [],
        'travel-agent' => ['admin.access', 'itineraries.manage', 'reservations.view', 'reservations.manage', 'support.manage'],
        'operations-officer' => ['admin.access', 'dashboard.view', 'departments.manage', 'availability.manage', 'reservations.view', 'reservations.manage', 'reservations.amend', 'partners.manage', 'support.manage'],
        'content-editor' => ['admin.access', 'destinations.manage', 'experiences.manage', 'content.manage'],
        'finance-officer' => ['admin.access', 'dashboard.view', 'payments.manage', 'refunds.manage', 'reports.view'],
        'partner-user' => ['admin.access', 'reservations.view'],
        'administrator' => self::PERMISSIONS,
        'super-administrator' => self::PERMISSIONS,
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, self::GUARD);
        }

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, self::GUARD);
            $role->syncPermissions($permissions);
        }
    }
}
