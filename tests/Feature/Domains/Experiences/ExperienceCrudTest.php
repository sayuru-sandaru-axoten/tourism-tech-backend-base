<?php

namespace Tests\Feature\Domains\Experiences;

use App\Domains\Destinations\Models\Category;
use App\Domains\Destinations\Models\Destination;
use App\Domains\Experiences\Enums\ExperienceStatus;
use App\Domains\Experiences\Models\Experience;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExperienceCrudTest extends TestCase
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
        Experience::factory()->create(['status' => ExperienceStatus::Draft->value]);
        Experience::factory()->published()->create();

        $this->actingAsStaff()
            ->getJson('/api/v1/admin/experiences')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_can_filter_by_status(): void
    {
        Experience::factory()->count(2)->create(['status' => ExperienceStatus::Draft->value]);
        Experience::factory()->published()->create();

        $this->actingAsStaff()
            ->getJson('/api/v1/admin/experiences?status=published')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_index_can_filter_by_destination(): void
    {
        $destination = Destination::factory()->create();
        Experience::factory()->create(['destination_id' => $destination->id]);
        Experience::factory()->create();

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/experiences?destination_id={$destination->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_index_can_filter_by_category(): void
    {
        $category = Category::factory()->create(['applies_to' => 'experience']);
        $tagged = Experience::factory()->create();
        $tagged->categories()->attach($category);
        Experience::factory()->create();

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/experiences?category_id={$category->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // ─── STORE ────────────────────────────────────────────────

    public function test_content_editor_can_create_an_experience(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff('content-editor')
            ->postJson('/api/v1/admin/experiences', [
                'destination_id' => $destination->id,
                'experience_type' => 'activity',
                'title' => 'Sigiriya Rock Climb',
            ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'sigiriya-rock-climb')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.published_at', null);
    }

    public function test_operations_officer_cannot_create_an_experience(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff('operations-officer')
            ->postJson('/api/v1/admin/experiences', [
                'destination_id' => $destination->id,
                'experience_type' => 'activity',
                'title' => 'Sigiriya Rock Climb',
            ])
            ->assertForbidden();
    }

    public function test_traveler_cannot_create_an_experience(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff('traveler')
            ->postJson('/api/v1/admin/experiences', [
                'destination_id' => $destination->id,
                'experience_type' => 'activity',
                'title' => 'Sigiriya Rock Climb',
            ])
            ->assertForbidden();
    }

    public function test_creating_as_published_sets_published_at(): void
    {
        $destination = Destination::factory()->create();

        $response = $this->actingAsStaff()
            ->postJson('/api/v1/admin/experiences', [
                'destination_id' => $destination->id,
                'experience_type' => 'activity',
                'title' => 'Sigiriya Rock Climb',
                'status' => 'published',
            ])
            ->assertCreated();

        $this->assertNotNull($response->json('data.published_at'));
    }

    public function test_status_must_be_a_valid_enum_value(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff()
            ->postJson('/api/v1/admin/experiences', [
                'destination_id' => $destination->id,
                'experience_type' => 'activity',
                'title' => 'Sigiriya Rock Climb',
                'status' => 'bogus',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_experience_type_must_be_a_valid_enum_value(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff()
            ->postJson('/api/v1/admin/experiences', [
                'destination_id' => $destination->id,
                'experience_type' => 'bogus',
                'title' => 'Sigiriya Rock Climb',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('experience_type');
    }

    public function test_destination_id_must_exist(): void
    {
        $this->actingAsStaff()
            ->postJson('/api/v1/admin/experiences', [
                'destination_id' => 999,
                'experience_type' => 'activity',
                'title' => 'Sigiriya Rock Climb',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('destination_id');
    }

    public function test_partner_id_must_exist_when_provided(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff()
            ->postJson('/api/v1/admin/experiences', [
                'destination_id' => $destination->id,
                'partner_id' => 999,
                'experience_type' => 'activity',
                'title' => 'Sigiriya Rock Climb',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('partner_id');
    }

    public function test_currency_requires_base_price_and_vice_versa(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff()
            ->postJson('/api/v1/admin/experiences', [
                'destination_id' => $destination->id,
                'experience_type' => 'activity',
                'title' => 'Sigiriya Rock Climb',
                'base_price' => 100,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('currency');
    }

    public function test_can_create_with_category_ids(): void
    {
        $destination = Destination::factory()->create();
        $category = Category::factory()->create(['applies_to' => 'experience']);

        $response = $this->actingAsStaff()
            ->postJson('/api/v1/admin/experiences', [
                'destination_id' => $destination->id,
                'experience_type' => 'activity',
                'title' => 'Sigiriya Rock Climb',
                'category_ids' => [$category->id],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('experience_category', [
            'experience_id' => $response->json('data.id'),
            'category_id' => $category->id,
        ]);
    }

    public function test_category_ids_must_apply_to_experience(): void
    {
        $destination = Destination::factory()->create();
        $destinationOnlyCategory = Category::factory()->create(['applies_to' => 'destination']);

        $this->actingAsStaff()
            ->postJson('/api/v1/admin/experiences', [
                'destination_id' => $destination->id,
                'experience_type' => 'activity',
                'title' => 'Sigiriya Rock Climb',
                'category_ids' => [$destinationOnlyCategory->id],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_ids.0');
    }

    // ─── UPDATE ───────────────────────────────────────────────

    public function test_updating_draft_to_published_sets_published_at(): void
    {
        $experience = Experience::factory()->create(['status' => ExperienceStatus::Draft->value, 'published_at' => null]);

        $response = $this->actingAsStaff()
            ->putJson("/api/v1/admin/experiences/{$experience->id}", ['status' => 'published'])
            ->assertOk();

        $this->assertNotNull($response->json('data.published_at'));
    }

    public function test_updating_published_back_to_draft_clears_published_at(): void
    {
        $experience = Experience::factory()->published()->create();

        $response = $this->actingAsStaff()
            ->putJson("/api/v1/admin/experiences/{$experience->id}", ['status' => 'draft'])
            ->assertOk();

        $this->assertNull($response->json('data.published_at'));
    }

    public function test_updating_title_reslug(): void
    {
        $experience = Experience::factory()->create(['title' => 'Old Title', 'slug' => 'old-title']);

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/experiences/{$experience->id}", ['title' => 'New Title'])
            ->assertOk()
            ->assertJsonPath('data.slug', 'new-title');
    }

    public function test_updating_category_ids_resyncs_pivot(): void
    {
        $experience = Experience::factory()->create();
        $first = Category::factory()->create(['applies_to' => 'experience']);
        $second = Category::factory()->create(['applies_to' => 'experience']);
        $experience->categories()->attach($first);

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/experiences/{$experience->id}", ['category_ids' => [$second->id]])
            ->assertOk();

        $this->assertDatabaseMissing('experience_category', ['experience_id' => $experience->id, 'category_id' => $first->id]);
        $this->assertDatabaseHas('experience_category', ['experience_id' => $experience->id, 'category_id' => $second->id]);
    }

    public function test_updating_with_empty_category_ids_clears_pivot(): void
    {
        $experience = Experience::factory()->create();
        $category = Category::factory()->create(['applies_to' => 'experience']);
        $experience->categories()->attach($category);

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/experiences/{$experience->id}", ['category_ids' => []])
            ->assertOk();

        $this->assertDatabaseMissing('experience_category', ['experience_id' => $experience->id, 'category_id' => $category->id]);
    }

    // ─── DESTROY ──────────────────────────────────────────────

    public function test_staff_can_soft_delete_an_experience(): void
    {
        $experience = Experience::factory()->create();

        $this->actingAsStaff()
            ->deleteJson("/api/v1/admin/experiences/{$experience->id}")
            ->assertOk();

        $this->assertSoftDeleted('experiences', ['id' => $experience->id]);
    }

    public function test_soft_deleted_experience_is_excluded_from_admin_index(): void
    {
        $experience = Experience::factory()->create();
        $experience->delete();

        $this->actingAsStaff()
            ->getJson('/api/v1/admin/experiences')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
