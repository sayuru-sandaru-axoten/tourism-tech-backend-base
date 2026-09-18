<?php

namespace Tests\Feature\Domains\Identity;

use App\Domains\Identity\Models\Department;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TravelAccessSeeder::class);
    }

    // ─── Helpers ──────────────────────────────────────────────

    private function staffUser(string $role = 'administrator'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function actingAsStaff(string $role = 'administrator'): self
    {
        return $this->actingAs($this->staffUser($role), 'api');
    }

    // ─── INDEX ────────────────────────────────────────────────

    public function test_staff_with_permission_can_list_departments(): void
    {
        Department::factory()->count(3)->create();

        $this->actingAsStaff()
            ->getJson('/api/v1/admin/departments')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_departments_are_ordered_by_name(): void
    {
        Department::factory()->create(['name' => 'Zephyr', 'slug' => 'zephyr']);
        Department::factory()->create(['name' => 'Alpha', 'slug' => 'alpha']);

        $names = $this->actingAsStaff()
            ->getJson('/api/v1/admin/departments')
            ->assertOk()
            ->json('data.*.name');

        $this->assertEquals(['Alpha', 'Zephyr'], $names);
    }

    public function test_unauthenticated_user_cannot_list_departments(): void
    {
        $this->getJson('/api/v1/admin/departments')
            ->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_list_departments(): void
    {
        $this->actingAsStaff('traveler')
            ->getJson('/api/v1/admin/departments')
            ->assertForbidden();
    }

    // ─── STORE ────────────────────────────────────────────────

    public function test_staff_can_create_a_department(): void
    {
        $this->actingAsStaff()
            ->postJson('/api/v1/admin/departments', [
                'name' => 'Operations',
                'description' => 'Field operations team',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Operations')
            ->assertJsonPath('data.slug', 'operations')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('departments', ['slug' => 'operations']);
    }

    public function test_department_name_must_be_unique(): void
    {
        Department::factory()->create(['name' => 'Finance', 'slug' => 'finance']);

        $this->actingAsStaff()
            ->postJson('/api/v1/admin/departments', ['name' => 'Finance'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_department_name_is_required(): void
    {
        $this->actingAsStaff()
            ->postJson('/api/v1/admin/departments', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    // ─── SHOW ─────────────────────────────────────────────────

    public function test_staff_can_view_a_single_department(): void
    {
        $department = Department::factory()->create();

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/departments/{$department->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $department->id)
            ->assertJsonPath('data.name', $department->name);
    }

    public function test_show_returns_404_for_missing_department(): void
    {
        $this->actingAsStaff()
            ->getJson('/api/v1/admin/departments/9999')
            ->assertNotFound();
    }

    // ─── UPDATE ───────────────────────────────────────────────

    public function test_staff_can_update_a_department(): void
    {
        $department = Department::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/departments/{$department->id}", [
                'name' => 'New Name',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.slug', 'new-name');
    }

    public function test_update_rejects_duplicate_name(): void
    {
        Department::factory()->create(['name' => 'Taken', 'slug' => 'taken']);
        $department = Department::factory()->create(['name' => 'Other', 'slug' => 'other']);

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/departments/{$department->id}", ['name' => 'Taken'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_update_allows_keeping_own_name(): void
    {
        $department = Department::factory()->create(['name' => 'Finance', 'slug' => 'finance']);

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/departments/{$department->id}", [
                'name' => 'Finance',
                'description' => 'Updated description',
            ])
            ->assertOk();
    }

    // ─── DESTROY ──────────────────────────────────────────────

    public function test_staff_can_delete_a_department(): void
    {
        $department = Department::factory()->create();

        $this->actingAsStaff()
            ->deleteJson("/api/v1/admin/departments/{$department->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Department deleted.');

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    public function test_deleting_department_nullifies_staff_profiles(): void
    {
        $department = Department::factory()->create();
        $staff = \App\Domains\Identity\Models\StaffProfile::factory()->create([
            'department_id' => $department->id,
        ]);

        $this->actingAsStaff()
            ->deleteJson("/api/v1/admin/departments/{$department->id}")
            ->assertOk();

        $this->assertNull($staff->fresh()?->department_id);
    }

    // ─── PERMISSION GATING ────────────────────────────────────

    public function test_operations_officer_can_manage_departments(): void
    {
        $this->actingAsStaff('operations-officer')
            ->postJson('/api/v1/admin/departments', ['name' => 'New Dept'])
            ->assertCreated();
    }

    public function test_content_editor_cannot_manage_departments(): void
    {
        $this->actingAsStaff('content-editor')
            ->postJson('/api/v1/admin/departments', ['name' => 'New Dept'])
            ->assertForbidden();
    }
}
