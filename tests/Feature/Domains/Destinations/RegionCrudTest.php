<?php

namespace Tests\Feature\Domains\Destinations;

use App\Domains\Destinations\Models\Country;
use App\Domains\Destinations\Models\Region;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionCrudTest extends TestCase
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

    public function test_staff_can_list_regions(): void
    {
        Region::factory()->count(3)->create();

        $this->actingAsStaff()
            ->getJson('/api/v1/admin/regions')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_index_can_filter_by_country_id(): void
    {
        $country = Country::factory()->create();
        Region::factory()->count(2)->create(['country_id' => $country->id]);
        Region::factory()->create();

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/regions?country_id={$country->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    // ─── STORE ────────────────────────────────────────────────

    public function test_staff_can_create_a_region(): void
    {
        $country = Country::factory()->create();

        $this->actingAsStaff()
            ->postJson('/api/v1/admin/regions', ['country_id' => $country->id, 'name' => 'Southern Province'])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'southern-province');
    }

    public function test_country_id_must_exist(): void
    {
        $this->actingAsStaff()
            ->postJson('/api/v1/admin/regions', ['country_id' => 999, 'name' => 'Nowhere'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('country_id');
    }

    public function test_slug_can_repeat_across_different_countries(): void
    {
        $countryA = Country::factory()->create();
        $countryB = Country::factory()->create();

        Region::factory()->create(['country_id' => $countryA->id, 'slug' => 'central']);

        $this->actingAsStaff()
            ->postJson('/api/v1/admin/regions', [
                'country_id' => $countryB->id,
                'name' => 'Central',
                'slug' => 'central',
            ])
            ->assertCreated();
    }

    public function test_slug_must_be_unique_within_the_same_country(): void
    {
        $country = Country::factory()->create();
        Region::factory()->create(['country_id' => $country->id, 'slug' => 'central']);

        $this->actingAsStaff()
            ->postJson('/api/v1/admin/regions', [
                'country_id' => $country->id,
                'name' => 'Central',
                'slug' => 'central',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slug');
    }

    // ─── UPDATE / DESTROY ───────────────────────────────────────

    public function test_staff_can_update_a_region(): void
    {
        $region = Region::factory()->create();

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/regions/{$region->id}", ['name' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    }

    public function test_staff_can_delete_a_region(): void
    {
        $region = Region::factory()->create();

        $this->actingAsStaff()
            ->deleteJson("/api/v1/admin/regions/{$region->id}")
            ->assertOk();

        $this->assertDatabaseMissing('regions', ['id' => $region->id]);
    }
}
