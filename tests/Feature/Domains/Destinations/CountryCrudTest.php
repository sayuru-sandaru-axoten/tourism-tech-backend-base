<?php

namespace Tests\Feature\Domains\Destinations;

use App\Domains\Destinations\Models\Country;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryCrudTest extends TestCase
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

    public function test_staff_with_permission_can_list_countries(): void
    {
        Country::factory()->count(3)->create();

        $this->actingAsStaff()
            ->getJson('/api/v1/admin/countries')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_unauthenticated_user_cannot_list_countries(): void
    {
        $this->getJson('/api/v1/admin/countries')->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_list_countries(): void
    {
        $this->actingAsStaff('traveler')
            ->getJson('/api/v1/admin/countries')
            ->assertForbidden();
    }

    // ─── STORE ────────────────────────────────────────────────

    public function test_content_editor_can_create_a_country(): void
    {
        $this->actingAsStaff('content-editor')
            ->postJson('/api/v1/admin/countries', ['name' => 'Sri Lanka', 'iso_code' => 'LK'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Sri Lanka')
            ->assertJsonPath('data.iso_code', 'LK');

        $this->assertDatabaseHas('countries', ['iso_code' => 'LK']);
    }

    public function test_operations_officer_cannot_create_a_country(): void
    {
        $this->actingAsStaff('operations-officer')
            ->postJson('/api/v1/admin/countries', ['name' => 'Sri Lanka', 'iso_code' => 'LK'])
            ->assertForbidden();
    }

    public function test_iso_code_must_be_unique(): void
    {
        Country::factory()->create(['iso_code' => 'LK']);

        $this->actingAsStaff()
            ->postJson('/api/v1/admin/countries', ['name' => 'Another', 'iso_code' => 'LK'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('iso_code');
    }

    public function test_name_and_iso_code_are_required(): void
    {
        $this->actingAsStaff()
            ->postJson('/api/v1/admin/countries', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'iso_code']);
    }

    // ─── SHOW ─────────────────────────────────────────────────

    public function test_staff_can_view_a_single_country(): void
    {
        $country = Country::factory()->create();

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/countries/{$country->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $country->id);
    }

    // ─── UPDATE ───────────────────────────────────────────────

    public function test_staff_can_update_a_country(): void
    {
        $country = Country::factory()->create(['name' => 'Old', 'iso_code' => 'AA']);

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/countries/{$country->id}", ['name' => 'New'])
            ->assertOk()
            ->assertJsonPath('data.name', 'New');
    }

    public function test_update_allows_keeping_own_iso_code(): void
    {
        $country = Country::factory()->create(['iso_code' => 'LK']);

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/countries/{$country->id}", ['iso_code' => 'LK'])
            ->assertOk();
    }

    // ─── DESTROY ──────────────────────────────────────────────

    public function test_staff_can_delete_a_country(): void
    {
        $country = Country::factory()->create();

        $this->actingAsStaff()
            ->deleteJson("/api/v1/admin/countries/{$country->id}")
            ->assertOk();

        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }
}
