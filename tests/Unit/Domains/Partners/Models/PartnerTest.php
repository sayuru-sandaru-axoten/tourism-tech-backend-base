<?php

namespace Tests\Unit\Domains\Partners\Models;

use App\Domains\Partners\Enums\PartnerType;
use App\Domains\Partners\Models\Partner;
use App\Domains\Partners\Models\PartnerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_type_is_cast_to_enum(): void
    {
        $partner = Partner::factory()->create(['partner_type' => PartnerType::Hotel->value]);

        $this->assertSame(PartnerType::Hotel, $partner->fresh()->partner_type);
    }

    public function test_partner_has_many_profiles(): void
    {
        $partner = Partner::factory()->create();
        $profile = PartnerProfile::factory()->create(['partner_id' => $partner->id]);

        $this->assertTrue($partner->profiles->contains($profile));
    }
}
