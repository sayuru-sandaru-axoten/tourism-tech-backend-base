<?php

namespace Tests\Feature\Domains\Destinations;

use App\Domains\Destinations\Enums\DestinationStatus;
use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Region;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationCrudTest extends TestCase
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

    private function actingAsStaff(string $role = 'administrator'): self
    {
        return $this->actingAs($this->staffUser($role), 'api');
    }

    // ─── INDEX ────────────────────────────────────────────────

    public function test_admin_index_lists_all_statuses(): void
    {
        Destination::factory()->create(['status' => DestinationStatus::Draft->value]);
        Destination::factory()->published()->create();

        $this->actingAsStaff()
            ->getJson('/api/v1/admin/destinations')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_can_filter_by_status(): void
    {
        Destination::factory()->count(2)->create(['status' => DestinationStatus::Draft->value]);
        Destination::factory()->published()->create();

        $this->actingAsStaff()
            ->getJson('/api/v1/admin/destinations?status=published')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // ─── STORE ────────────────────────────────────────────────

    public function test_content_editor_can_create_a_destination(): void
    {
        $region = Region::factory()->create();

        $this->actingAsStaff('content-editor')
            ->postJson('/api/v1/admin/destinations', [
                'region_id' => $region->id,
                'name' => 'Ella',
            ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'ella')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.published_at', null);
    }

    public function test_operations_officer_cannot_create_a_destination(): void
    {
        $region = Region::factory()->create();

        $this->actingAsStaff('operations-officer')
            ->postJson('/api/v1/admin/destinations', ['region_id' => $region->id, 'name' => 'Ella'])
            ->assertForbidden();
    }

    public function test_traveler_cannot_create_a_destination(): void
    {
        $region = Region::factory()->create();

        $this->actingAsStaff('traveler')
            ->postJson('/api/v1/admin/destinations', ['region_id' => $region->id, 'name' => 'Ella'])
            ->assertForbidden();
    }

    public function test_creating_as_published_sets_published_at(): void
    {
        $region = Region::factory()->create();

        $response = $this->actingAsStaff()
            ->postJson('/api/v1/admin/destinations', [
                'region_id' => $region->id,
                'name' => 'Ella',
                'status' => 'published',
            ])
            ->assertCreated();

        $this->assertNotNull($response->json('data.published_at'));
    }

    public function test_status_must_be_a_valid_enum_value(): void
    {
        $region = Region::factory()->create();

        $this->actingAsStaff()
            ->postJson('/api/v1/admin/destinations', [
                'region_id' => $region->id,
                'name' => 'Ella',
                'status' => 'bogus',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_region_id_must_exist(): void
    {
        $this->actingAsStaff()
            ->postJson('/api/v1/admin/destinations', ['region_id' => 999, 'name' => 'Ella'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('region_id');
    }

    public function test_category_id_is_optional(): void
    {
        $region = Region::factory()->create();

        $this->actingAsStaff()
            ->postJson('/api/v1/admin/destinations', ['region_id' => $region->id, 'name' => 'Ella'])
            ->assertCreated()
            ->assertJsonPath('data.category_id', null);
    }

    // ─── UPDATE ───────────────────────────────────────────────

    public function test_updating_draft_to_published_sets_published_at(): void
    {
        $destination = Destination::factory()->create(['status' => DestinationStatus::Draft->value, 'published_at' => null]);

        $response = $this->actingAsStaff()
            ->putJson("/api/v1/admin/destinations/{$destination->id}", ['status' => 'published'])
            ->assertOk();

        $this->assertNotNull($response->json('data.published_at'));
    }

    public function test_updating_published_back_to_draft_clears_published_at(): void
    {
        $destination = Destination::factory()->published()->create();

        $response = $this->actingAsStaff()
            ->putJson("/api/v1/admin/destinations/{$destination->id}", ['status' => 'draft'])
            ->assertOk();

        $this->assertNull($response->json('data.published_at'));
    }

    public function test_updating_name_reslug(): void
    {
        $destination = Destination::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/destinations/{$destination->id}", ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonPath('data.slug', 'new-name');
    }

    // ─── DESTROY ──────────────────────────────────────────────

    public function test_staff_can_soft_delete_a_destination(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff()
            ->deleteJson("/api/v1/admin/destinations/{$destination->id}")
            ->assertOk();

        $this->assertSoftDeleted('destinations', ['id' => $destination->id]);
    }

    public function test_soft_deleted_destination_is_excluded_from_admin_index(): void
    {
        $destination = Destination::factory()->create();
        $destination->delete();

        $this->actingAsStaff()
            ->getJson('/api/v1/admin/destinations')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
