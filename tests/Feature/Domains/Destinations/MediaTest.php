<?php

namespace Tests\Feature\Domains\Destinations;

use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Media;
use App\Domains\Destinations\Models\Place;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TravelAccessSeeder::class);
        Storage::fake('public');
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

    // ─── UPLOAD ───────────────────────────────────────────────

    public function test_staff_can_upload_media_to_a_destination(): void
    {
        $destination = Destination::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->actingAsStaff()
            ->postJson("/api/v1/admin/destinations/{$destination->id}/media", [
                'file' => $file,
                'alt_text' => 'A nice view',
            ])
            ->assertCreated()
            ->assertJsonPath('data.media_type', 'image')
            ->assertJsonPath('data.alt_text', 'A nice view');

        $path = $response->json('data.path');
        Storage::disk('public')->assertExists($path);
    }

    public function test_staff_can_upload_media_to_a_place(): void
    {
        $place = Place::factory()->create();
        $file = UploadedFile::fake()->create('clip.mp4', 500, 'video/mp4');

        $this->actingAsStaff()
            ->postJson("/api/v1/admin/places/{$place->id}/media", ['file' => $file])
            ->assertCreated()
            ->assertJsonPath('data.media_type', 'video');
    }

    public function test_upload_rejects_oversized_file(): void
    {
        $destination = Destination::factory()->create();
        $file = UploadedFile::fake()->create('big.jpg', 20000, 'image/jpeg');

        $this->actingAsStaff()
            ->postJson("/api/v1/admin/destinations/{$destination->id}/media", ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_upload_rejects_disallowed_mime_type(): void
    {
        $destination = Destination::factory()->create();
        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $this->actingAsStaff()
            ->postJson("/api/v1/admin/destinations/{$destination->id}/media", ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    // ─── INDEX (scoped) ───────────────────────────────────────

    public function test_media_index_is_scoped_to_its_mediable(): void
    {
        $destination = Destination::factory()->create();
        $place = Place::factory()->create();

        Media::factory()->count(2)->create(['mediable_type' => 'destination', 'mediable_id' => $destination->id]);
        Media::factory()->create(['mediable_type' => 'place', 'mediable_id' => $place->id]);

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/destinations/{$destination->id}/media")
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/places/{$place->id}/media")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // ─── DESTROY ──────────────────────────────────────────────

    public function test_staff_can_delete_media_and_its_stored_file(): void
    {
        $destination = Destination::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg');
        $path = $file->store('destinations-media', 'public');

        $media = Media::factory()->create([
            'mediable_type' => 'destination',
            'mediable_id' => $destination->id,
            'path' => $path,
        ]);

        $this->actingAsStaff()
            ->deleteJson("/api/v1/admin/media/{$media->id}")
            ->assertOk();

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($path);
    }

    // ─── REORDER ──────────────────────────────────────────────

    public function test_staff_can_reorder_media(): void
    {
        $destination = Destination::factory()->create();
        $first = Media::factory()->create(['mediable_type' => 'destination', 'mediable_id' => $destination->id, 'sort_order' => 0]);
        $second = Media::factory()->create(['mediable_type' => 'destination', 'mediable_id' => $destination->id, 'sort_order' => 1]);

        $this->actingAsStaff()
            ->patchJson('/api/v1/admin/media/reorder', [
                'items' => [
                    ['id' => $first->id, 'sort_order' => 1],
                    ['id' => $second->id, 'sort_order' => 0],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('media', ['id' => $first->id, 'sort_order' => 1]);
        $this->assertDatabaseHas('media', ['id' => $second->id, 'sort_order' => 0]);
    }

    public function test_unauthenticated_cannot_upload_media(): void
    {
        $destination = Destination::factory()->create();

        $this->postJson("/api/v1/admin/destinations/{$destination->id}/media", [
            'file' => UploadedFile::fake()->image('photo.jpg'),
        ])->assertUnauthorized();
    }
}
