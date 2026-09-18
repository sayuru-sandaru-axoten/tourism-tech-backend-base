<?php

namespace Tests\Feature\Domains\Destinations;

use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Place;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceCrudTest extends TestCase
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

    // ─── INDEX / STORE (nested) ─────────────────────────────────

    public function test_staff_can_list_places_for_a_destination(): void
    {
        $destination = Destination::factory()->create();
        Place::factory()->count(2)->create(['destination_id' => $destination->id]);
        Place::factory()->create(); // belongs to a different destination

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/destinations/{$destination->id}/places")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_staff_can_create_a_place_under_a_destination(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff()
            ->postJson("/api/v1/admin/destinations/{$destination->id}/places", [
                'name' => 'Nine Arch Bridge',
                'place_type' => 'landmark',
            ])
            ->assertCreated()
            ->assertJsonPath('data.destination_id', $destination->id);
    }

    public function test_place_type_must_be_a_valid_value(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff()
            ->postJson("/api/v1/admin/destinations/{$destination->id}/places", [
                'name' => 'Somewhere',
                'place_type' => 'bogus',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('place_type');
    }

    public function test_unauthenticated_cannot_list_places(): void
    {
        $destination = Destination::factory()->create();

        $this->getJson("/api/v1/admin/destinations/{$destination->id}/places")
            ->assertUnauthorized();
    }

    // ─── SHOW / UPDATE / DESTROY (shallow) ───────────────────────

    public function test_staff_can_view_a_place_via_shallow_route(): void
    {
        $place = Place::factory()->create();

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/places/{$place->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $place->id);
    }

    public function test_staff_can_update_a_place(): void
    {
        $place = Place::factory()->create(['name' => 'Old Name']);

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/places/{$place->id}", ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_staff_can_delete_a_place(): void
    {
        $place = Place::factory()->create();

        $this->actingAsStaff()
            ->deleteJson("/api/v1/admin/places/{$place->id}")
            ->assertOk();

        $this->assertDatabaseMissing('places', ['id' => $place->id]);
    }

    public function test_places_survive_a_soft_deleted_parent_destination(): void
    {
        $destination = Destination::factory()->create();
        $place = Place::factory()->create(['destination_id' => $destination->id]);

        $destination->delete();

        $this->assertDatabaseHas('places', ['id' => $place->id]);
    }
}
