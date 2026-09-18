<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Policies are polymorphic (cancellation / payment / age_requirement /
     * health_safety) so a future domain — Reservations, for a booking-time
     * snapshot — can reuse this table without a schema change. Owned by the
     * Experiences domain for now, the only current `policyable`.
     *
     * The unique constraint prevents two "cancellation" policies on the same
     * owner and doubles as a natural upsert key.
     */
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table): void {
            $table->id();
            $table->string('policyable_type');
            $table->unsignedBigInteger('policyable_id');
            $table->string('policy_type');
            $table->string('title');
            $table->text('body');
            $table->timestamps();

            $table->index(['policyable_type', 'policyable_id']);
            $table->unique(['policyable_type', 'policyable_id', 'policy_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
