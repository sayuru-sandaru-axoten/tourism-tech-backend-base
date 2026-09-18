<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Experiences are what a traveler actually books — packages, activities,
     * stays, transfers, and guide services hosted on a destination. `status`
     * drives the same content workflow as `destinations`
     * (draft -> review -> scheduled -> published -> archived); `published_at`
     * is set/cleared by the domain's actions whenever status crosses into or
     * out of "published", never accepted directly as input.
     *
     * `experience_type` carries one `base_price`/`currency` for card display
     * only — the real bookable price comes from Pricing's `rate_plans` in
     * Phase 2 and is never wired to real booking math here.
     *
     * `experience_type`/`status` (and `policy_type` on the `policies` table)
     * are backed by PHP enums, matching `DestinationStatus`/`PartnerType`'s
     * precedent, unlike `places.place_type` which stays a plain validated
     * string — the enum-backed style is the norm in this codebase.
     */
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->string('experience_type');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->unsignedInteger('duration_nights')->nullable();
            $table->unsignedInteger('min_group_size')->nullable();
            $table->unsignedInteger('max_group_size')->nullable();
            $table->decimal('base_price', 12, 2)->nullable();
            $table->char('currency', 3)->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
