<?php

namespace Tests\Feature\Domains\Identity;

use App\Domains\Identity\Models\Department;
use App\Domains\Partners\Models\Partner;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TravelAccessSeeder::class);
    }

    private function staffUser(string $role = 'administrator'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    // ─── Traveler self-service ───────────────────────────────

    public function test_traveler_can_view_own_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('traveler');
        $user->travelerProfile()->create([]);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/me/profile')
            ->assertOk()
            ->assertJsonPath('data.nationality', null);
    }

    public function test_traveler_can_update_own_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('traveler');
        $user->travelerProfile()->create([]);

        $this->actingAs($user, 'api')
            ->patchJson('/api/v1/me/profile', [
                'nationality' => 'Sri Lankan',
                'emergency_contact_name' => 'Jane Doe',
            ])
            ->assertOk()
            ->assertJsonPath('data.nationality', 'Sri Lankan')
            ->assertJsonPath('data.emergency_contact_name', 'Jane Doe');

        $this->assertDatabaseHas('traveler_profiles', [
            'user_id' => $user->id,
            'nationality' => 'Sri Lankan',
        ]);
    }

    public function test_staff_user_without_traveler_profile_gets_404(): void
    {
        $this->actingAs($this->staffUser(), 'api')
            ->getJson('/api/v1/me/profile')
            ->assertNotFound();
    }

    // ─── Staff account creation ───────────────────────────────

    public function test_admin_can_create_staff_account(): void
    {
        $department = Department::factory()->create();

        $this->actingAs($this->staffUser(), 'api')
            ->postJson('/api/v1/admin/staff', [
                'name' => 'New Staffer',
                'email' => 'staffer@example.com',
                'password' => 'password12345',
                'role' => 'operations-officer',
                'department_id' => $department->id,
                'employee_code' => 'EMP-0001',
            ])
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'staffer@example.com')
            ->assertJsonPath('data.employee_code', 'EMP-0001');

        $this->assertDatabaseHas('users', ['email' => 'staffer@example.com']);
    }

    public function test_newly_created_staff_account_can_log_in(): void
    {
        $this->actingAs($this->staffUser(), 'api')
            ->postJson('/api/v1/admin/staff', [
                'name' => 'New Staffer',
                'email' => 'newstaffer@example.com',
                'password' => 'password12345',
                'role' => 'operations-officer',
            ])
            ->assertCreated();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'newstaffer@example.com',
            'password' => 'password12345',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.email', 'newstaffer@example.com')
            ->assertJsonPath('data.user.roles.0', 'operations-officer')
            ->assertJsonStructure(['data' => ['access_token']]);
    }

    public function test_staff_creation_rejects_traveler_role(): void
    {
        $this->actingAs($this->staffUser(), 'api')
            ->postJson('/api/v1/admin/staff', [
                'name' => 'New Staffer',
                'email' => 'staffer2@example.com',
                'password' => 'password12345',
                'role' => 'traveler',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('role');
    }

    public function test_user_without_permission_cannot_create_staff_account(): void
    {
        $this->actingAs($this->staffUser('content-editor'), 'api')
            ->postJson('/api/v1/admin/staff', [
                'name' => 'New Staffer',
                'email' => 'staffer3@example.com',
                'password' => 'password12345',
                'role' => 'operations-officer',
            ])
            ->assertForbidden();
    }

    // ─── Partner account creation ─────────────────────────────

    public function test_admin_can_create_partner_user_with_new_partner(): void
    {
        $this->actingAs($this->staffUser(), 'api')
            ->postJson('/api/v1/admin/partners/users', [
                'name' => 'Partner Person',
                'email' => 'partner@example.com',
                'password' => 'password12345',
                'job_title' => 'Manager',
                'partner_name' => 'Hilltop Hotel',
                'partner_type' => 'hotel',
            ])
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'partner@example.com')
            ->assertJsonPath('data.partner.name', 'Hilltop Hotel');

        $this->assertDatabaseHas('partners', ['name' => 'Hilltop Hotel']);
    }

    public function test_admin_can_create_partner_user_for_existing_partner(): void
    {
        $partner = Partner::factory()->create();

        $this->actingAs($this->staffUser(), 'api')
            ->postJson('/api/v1/admin/partners/users', [
                'name' => 'Partner Person Two',
                'email' => 'partner2@example.com',
                'password' => 'password12345',
                'partner_id' => $partner->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.partner.id', $partner->id);
    }

    public function test_partner_creation_requires_partner_reference(): void
    {
        $this->actingAs($this->staffUser(), 'api')
            ->postJson('/api/v1/admin/partners/users', [
                'name' => 'Partner Person',
                'email' => 'partner3@example.com',
                'password' => 'password12345',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['partner_name', 'partner_type']);
    }
}
