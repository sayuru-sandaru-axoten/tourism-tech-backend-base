<?php

namespace Tests\Unit\Domains\Identity\Models;

use App\Domains\Identity\Models\TravelerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TravelerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_passport_number_is_stored_encrypted(): void
    {
        $profile = TravelerProfile::factory()->create(['passport_number' => 'N1234567']);

        $rawValue = DB::table('traveler_profiles')->where('id', $profile->id)->value('passport_number');

        $this->assertNotSame('N1234567', $rawValue);
        $this->assertSame('N1234567', $profile->fresh()->passport_number);
    }
}
