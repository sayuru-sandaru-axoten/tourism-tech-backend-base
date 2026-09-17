<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Media is polymorphic and shared platform-wide — defined once here and
     * reused by Destinations and Places (and later Experiences) instead of
     * a separate image table per domain. `mediable_type` stores the short
     * morph-map alias, not the fully-qualified class name.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('media_type');
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
