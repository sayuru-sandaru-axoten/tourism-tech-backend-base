<?php

namespace Tests\Feature\Domains\Experiences;

use App\Domains\Experiences\Models\Experience;
use App\Domains\Experiences\Models\Policy;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyCrudTest extends TestCase
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

    public function test_staff_can_list_policies_for_an_experience(): void
    {
        $experience = Experience::factory()->create();
        Policy::factory()->create(['policyable_type' => 'experience', 'policyable_id' => $experience->id, 'policy_type' => 'cancellation']);
        Policy::factory()->create(['policyable_type' => 'experience', 'policyable_id' => $experience->id, 'policy_type' => 'payment']);
        Policy::factory()->create(); // belongs to a different experience

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/experiences/{$experience->id}/policies")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_staff_can_create_a_policy_under_an_experience(): void
    {
        $experience = Experience::factory()->create();

        $this->actingAsStaff()
            ->postJson("/api/v1/admin/experiences/{$experience->id}/policies", [
                'policy_type' => 'cancellation',
                'title' => 'Cancellation Policy',
                'body' => 'Free cancellation up to 24 hours before.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.policy_type', 'cancellation');
    }

    public function test_policy_type_must_be_a_valid_enum_value(): void
    {
        $experience = Experience::factory()->create();

        $this->actingAsStaff()
            ->postJson("/api/v1/admin/experiences/{$experience->id}/policies", [
                'policy_type' => 'bogus',
                'title' => 'Cancellation Policy',
                'body' => 'Free cancellation up to 24 hours before.',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('policy_type');
    }

    public function test_a_second_policy_of_the_same_type_is_rejected(): void
    {
        $experience = Experience::factory()->create();
        Policy::factory()->create(['policyable_type' => 'experience', 'policyable_id' => $experience->id, 'policy_type' => 'cancellation']);

        $this->actingAsStaff()
            ->postJson("/api/v1/admin/experiences/{$experience->id}/policies", [
                'policy_type' => 'cancellation',
                'title' => 'Another Cancellation Policy',
                'body' => 'Different body.',
            ])
            ->assertServerError();
    }

    public function test_unauthenticated_cannot_list_policies(): void
    {
        $experience = Experience::factory()->create();

        $this->getJson("/api/v1/admin/experiences/{$experience->id}/policies")
            ->assertUnauthorized();
    }

    // ─── SHOW / UPDATE / DESTROY (shallow) ───────────────────────

    public function test_staff_can_view_a_policy_via_shallow_route(): void
    {
        $policy = Policy::factory()->create();

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/policies/{$policy->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $policy->id);
    }

    public function test_staff_can_update_a_policy(): void
    {
        $policy = Policy::factory()->create(['title' => 'Old Title']);

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/policies/{$policy->id}", ['title' => 'New Title'])
            ->assertOk()
            ->assertJsonPath('data.title', 'New Title');
    }

    public function test_staff_can_delete_a_policy(): void
    {
        $policy = Policy::factory()->create();

        $this->actingAsStaff()
            ->deleteJson("/api/v1/admin/policies/{$policy->id}")
            ->assertOk();

        $this->assertDatabaseMissing('policies', ['id' => $policy->id]);
    }

    public function test_policies_survive_a_soft_deleted_parent_experience(): void
    {
        $experience = Experience::factory()->create();
        $policy = Policy::factory()->create(['policyable_type' => 'experience', 'policyable_id' => $experience->id]);

        $experience->delete();

        $this->assertDatabaseHas('policies', ['id' => $policy->id]);
    }
}
