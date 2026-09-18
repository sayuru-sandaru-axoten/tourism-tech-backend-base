<?php

namespace Tests\Feature\Domains\Destinations;

use App\Domains\Destinations\Models\Category;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
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

    public function test_staff_can_list_categories(): void
    {
        Category::factory()->count(3)->create();

        $this->actingAsStaff()
            ->getJson('/api/v1/admin/categories')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_index_can_filter_by_applies_to(): void
    {
        Category::factory()->count(2)->create(['applies_to' => 'destination']);
        Category::factory()->forExperience()->create();

        $this->actingAsStaff()
            ->getJson('/api/v1/admin/categories?applies_to=experience')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // ─── STORE ────────────────────────────────────────────────

    public function test_staff_can_create_a_category(): void
    {
        $this->actingAsStaff()
            ->postJson('/api/v1/admin/categories', ['name' => 'Beach', 'applies_to' => 'destination'])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'beach');
    }

    public function test_applies_to_must_be_a_valid_value(): void
    {
        $this->actingAsStaff()
            ->postJson('/api/v1/admin/categories', ['name' => 'Beach', 'applies_to' => 'invalid'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('applies_to');
    }

    public function test_slug_must_be_unique_globally(): void
    {
        Category::factory()->create(['slug' => 'beach']);

        $this->actingAsStaff()
            ->postJson('/api/v1/admin/categories', ['name' => 'Beach', 'slug' => 'beach', 'applies_to' => 'destination'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slug');
    }

    // ─── UPDATE / DESTROY ───────────────────────────────────────

    public function test_staff_can_update_a_category(): void
    {
        $category = Category::factory()->create();

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/categories/{$category->id}", ['name' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    }

    public function test_staff_can_delete_a_category(): void
    {
        $category = Category::factory()->create();

        $this->actingAsStaff()
            ->deleteJson("/api/v1/admin/categories/{$category->id}")
            ->assertOk();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
