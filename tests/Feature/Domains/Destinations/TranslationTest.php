<?php

namespace Tests\Feature\Domains\Destinations;

use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Place;
use App\Domains\Destinations\Models\Translation;
use App\Domains\Experiences\Models\Experience;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationTest extends TestCase
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

    // ─── UPSERT ───────────────────────────────────────────────

    public function test_upsert_creates_a_translation(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/destinations/{$destination->id}/translations", [
                'locale' => 'fr',
                'field_key' => 'description',
                'value' => 'Une belle destination',
            ])
            ->assertOk()
            ->assertJsonPath('data.locale', 'fr')
            ->assertJsonPath('data.value', 'Une belle destination');

        $this->assertDatabaseCount('translations', 1);
    }

    public function test_upsert_updates_the_same_locale_and_field_key_in_place(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/destinations/{$destination->id}/translations", [
                'locale' => 'fr', 'field_key' => 'description', 'value' => 'First',
            ])
            ->assertOk();

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/destinations/{$destination->id}/translations", [
                'locale' => 'fr', 'field_key' => 'description', 'value' => 'Second',
            ])
            ->assertOk()
            ->assertJsonPath('data.value', 'Second');

        $this->assertDatabaseCount('translations', 1);
        $this->assertDatabaseHas('translations', ['locale' => 'fr', 'field_key' => 'description', 'value' => 'Second']);
    }

    public function test_a_different_locale_creates_a_second_row(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/destinations/{$destination->id}/translations", [
                'locale' => 'fr', 'field_key' => 'description', 'value' => 'French',
            ])->assertOk();

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/destinations/{$destination->id}/translations", [
                'locale' => 'de', 'field_key' => 'description', 'value' => 'German',
            ])->assertOk();

        $this->assertDatabaseCount('translations', 2);
    }

    public function test_locale_format_is_validated(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/destinations/{$destination->id}/translations", [
                'locale' => 'not-a-locale', 'field_key' => 'description', 'value' => 'x',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('locale');
    }

    // ─── INDEX (scoped) ───────────────────────────────────────

    public function test_translations_index_is_scoped_to_its_translatable(): void
    {
        $destination = Destination::factory()->create();
        $place = Place::factory()->create();

        Translation::factory()->create(['translatable_type' => 'destination', 'translatable_id' => $destination->id, 'locale' => 'fr']);
        Translation::factory()->create(['translatable_type' => 'destination', 'translatable_id' => $destination->id, 'locale' => 'de']);
        Translation::factory()->create(['translatable_type' => 'place', 'translatable_id' => $place->id]);

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/destinations/{$destination->id}/translations")
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/places/{$place->id}/translations")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // ─── EXPERIENCE OWNER ─────────────────────────────────────

    public function test_staff_can_upsert_a_translation_for_an_experience(): void
    {
        $experience = Experience::factory()->create();

        $this->actingAsStaff()
            ->putJson("/api/v1/admin/experiences/{$experience->id}/translations", [
                'locale' => 'fr', 'field_key' => 'description', 'value' => 'Une belle experience',
            ])
            ->assertOk()
            ->assertJsonPath('data.locale', 'fr');

        $this->assertDatabaseHas('translations', [
            'translatable_type' => 'experience',
            'translatable_id' => $experience->id,
            'locale' => 'fr',
        ]);
    }

    public function test_experience_translations_index_is_scoped(): void
    {
        $experience = Experience::factory()->create();

        Translation::factory()->create(['translatable_type' => 'experience', 'translatable_id' => $experience->id, 'locale' => 'fr']);
        Translation::factory()->create(['translatable_type' => 'experience', 'translatable_id' => $experience->id, 'locale' => 'de']);
        Translation::factory()->create(); // different owner

        $this->actingAsStaff()
            ->getJson("/api/v1/admin/experiences/{$experience->id}/translations")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    // ─── DESTROY ──────────────────────────────────────────────

    public function test_staff_can_delete_a_translation(): void
    {
        $destination = Destination::factory()->create();
        $translation = Translation::factory()->create(['translatable_type' => 'destination', 'translatable_id' => $destination->id]);

        $this->actingAsStaff()
            ->deleteJson("/api/v1/admin/translations/{$translation->id}")
            ->assertOk();

        $this->assertDatabaseMissing('translations', ['id' => $translation->id]);
    }

    public function test_unauthenticated_cannot_upsert_translations(): void
    {
        $destination = Destination::factory()->create();

        $this->putJson("/api/v1/admin/destinations/{$destination->id}/translations", [
            'locale' => 'fr', 'field_key' => 'description', 'value' => 'x',
        ])->assertUnauthorized();
    }
}
