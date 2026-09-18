<?php

namespace Tests\Feature\Domains\Destinations;

use App\Domains\Destinations\Enums\DestinationStatus;
use App\Domains\Destinations\Models\Destination;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DestinationPublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TravelAccessSeeder::class);
    }

    public function test_published_destination_is_visible_in_public_index_without_auth(): void
    {
        Destination::factory()->published()->create();

        $this->getJson('/api/v1/destinations')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_published_destination_is_visible_via_public_show_without_auth(): void
    {
        $destination = Destination::factory()->published()->create();

        $this->getJson("/api/v1/destinations/{$destination->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $destination->slug);
    }

    /**
     * @return list<array{0: string}>
     */
    public static function nonPublishedStatuses(): array
    {
        return [
            [DestinationStatus::Draft->value],
            [DestinationStatus::Review->value],
            [DestinationStatus::Scheduled->value],
            [DestinationStatus::Archived->value],
        ];
    }

    #[DataProvider('nonPublishedStatuses')]
    public function test_non_published_destinations_are_excluded_from_public_index(string $status): void
    {
        Destination::factory()->create(['status' => $status]);

        $this->getJson('/api/v1/destinations')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    #[DataProvider('nonPublishedStatuses')]
    public function test_non_published_destination_404s_on_public_show(string $status): void
    {
        $destination = Destination::factory()->create(['status' => $status]);

        $this->getJson("/api/v1/destinations/{$destination->slug}")
            ->assertNotFound();
    }

    public function test_admin_index_still_returns_all_statuses(): void
    {
        Destination::factory()->create(['status' => DestinationStatus::Draft->value]);
        Destination::factory()->published()->create();

        $user = User::factory()->create();
        $user->assignRole('administrator');

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/admin/destinations')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
