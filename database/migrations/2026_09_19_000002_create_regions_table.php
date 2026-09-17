<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Regions subdivide a country (e.g. provinces) and are the level
     * that destinations attach to. `slug` only needs to be unique within
     * its own country, not platform-wide.
     */
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['country_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
