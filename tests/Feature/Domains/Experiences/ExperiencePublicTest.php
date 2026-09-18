<?php

namespace Tests\Feature\Domains\Experiences;

use App\Domains\Experiences\Enums\ExperienceStatus;
use App\Domains\Experiences\Models\Experience;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ExperiencePublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TravelAccessSeeder::class);
    }

    public function test_published_experience_is_visible_in_public_index_without_auth(): void
    {
        Experience::factory()->published()->create();

        $this->getJson('/api/v1/experiences')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_published_experience_is_visible_via_public_show_without_auth(): void
    {
        $experience = Experience::factory()->published()->create();

        $this->getJson("/api/v1/experiences/{$experience->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $experience->slug);
    }

    /**
     * @return list<array{0: string}>
     */
    public static function nonPublishedStatuses(): array
    {
        return [
            [ExperienceStatus::Draft->value],
            [ExperienceStatus::Review->value],
            [ExperienceStatus::Scheduled->value],
            [ExperienceStatus::Archived->value],
        ];
    }

    #[DataProvider('nonPublishedStatuses')]
    public function test_non_published_experiences_are_excluded_from_public_index(string $status): void
    {
        Experience::factory()->create(['status' => $status]);

        $this->getJson('/api/v1/experiences')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    #[DataProvider('nonPublishedStatuses')]
    public function test_non_published_experience_404s_on_public_show(string $status): void
    {
        $experience = Experience::factory()->create(['status' => $status]);

        $this->getJson("/api/v1/experiences/{$experience->slug}")
            ->assertNotFound();
    }

    public function test_admin_index_still_returns_all_statuses(): void
    {
        Experience::factory()->create(['status' => ExperienceStatus::Draft->value]);
        Experience::factory()->published()->create();

        $user = User::factory()->create();
        $user->assignRole('administrator');

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/admin/experiences')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
