<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One profile per user (`user_id` unique). `passport_number` is `text`, not
     * `string` — the model's `encrypted` cast produces ciphertext that can
     * exceed varchar(255) even for a short plaintext. `loyalty_tier` is a
     * placeholder column with no business logic yet (owned by the future
     * Loyalty domain), same as `users.status` existed as a permission string
     * before it was a real column.
     */
    public function up(): void
    {
        Schema::create('traveler_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->text('passport_number')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('loyalty_tier')->nullable();
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->json('preferences')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traveler_profiles');
    }
};
