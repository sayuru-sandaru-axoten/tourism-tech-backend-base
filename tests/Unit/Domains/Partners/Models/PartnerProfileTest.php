<?php

namespace Tests\Unit\Domains\Partners\Models;

use App\Domains\Partners\Models\Partner;
use App\Domains\Partners\Models\PartnerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_profile_belongs_to_user_and_partner(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->create();
        $profile = PartnerProfile::factory()->create([
            'user_id' => $user->id,
            'partner_id' => $partner->id,
        ]);

        $this->assertTrue($user->partnerProfile->is($profile));
        $this->assertTrue($profile->user->is($user));
        $this->assertTrue($profile->partner->is($partner));
    }
}
