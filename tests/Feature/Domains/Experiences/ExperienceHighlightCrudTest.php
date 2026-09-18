<?php

namespace Tests\Feature\Domains\Experiences;

use App\Domains\Experiences\Models\Experience;
use App\Domains\Experiences\Models\ExperienceHighlight;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExperienceHighlightCrudTest extends TestCase
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

    public function test_staff_can_list_highlights_for_an_experience(): void
    {
        $experience = Experience::factory()->create();
        ExperienceHighlight::factory()->count(2)->create(['experience_id' => $experience->id]);
        ExperienceHighlight::factory()->create(); // belongs to a different experience

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/experiences/{$experience->id}/highlights")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_staff_can_create_a_highlight_under_an_experience(): void
    {
        $experience = Experience::factory()->create();

        $this->actingAsStaff()
            ->postJson("/api/v1/admin/experiences/{$experience->id}/highlights", [
                'title' => 'Sigiriya Rock climb',
            ])
            ->assertCreated()
            ->assertJsonPath('data.experience_id', $experience->id);
    }

    public function test_title_is_required(): void
    {
        $experience = Experience::factory()->create();

        $this->actingAsStaff()
            ->postJson("/api/v1/admin/experiences/{$experience->id}/highlights", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_unauthenticated_cannot_list_highlights(): void
    {
        $experience = Experience::factory()->create();

        $this->getJson("/api/v1/admin/experiences/{$experience->id}/highlights")
            ->assertUnauthorized();
    }

    // ─── SHOW / UPDATE / DESTROY (shallow) ───────────────────────

    public function test_staff_can_view_a_highlight_via_shallow_route(): void
    {
        $highlight = ExperienceHighlight::factory()->create();

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/highlights/{$highlight->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $highlight->id);
    }

    public function test_staff_can_update_a_highlight(): void
    {
        $highlight = ExperienceHighlight::factory()->create(['title' => 'Old Title']);

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/highlights/{$highlight->id}", ['title' => 'New Title'])
            ->assertOk()
            ->assertJsonPath('data.title', 'New Title');
    }

    public function test_staff_can_delete_a_highlight(): void
    {
        $highlight = ExperienceHighlight::factory()->create();

        $this->actingAsStaff()
            ->deleteJson("/api/v1/admin/highlights/{$highlight->id}")
            ->assertOk();

        $this->assertDatabaseMissing('experience_highlights', ['id' => $highlight->id]);
    }

    public function test_highlights_survive_a_soft_deleted_parent_experience(): void
    {
        $experience = Experience::factory()->create();
        $highlight = ExperienceHighlight::factory()->create(['experience_id' => $experience->id]);

        $experience->delete();

        $this->assertDatabaseHas('experience_highlights', ['id' => $highlight->id]);
    }
}
