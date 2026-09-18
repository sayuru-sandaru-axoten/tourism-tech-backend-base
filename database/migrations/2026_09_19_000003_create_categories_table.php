<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Categories tag both destinations and experiences (e.g. Beach, Wildlife,
     * Historical). `applies_to` scopes a category to one of those two domains;
     * this table is shared platform-wide and the not-yet-built Experiences
     * domain will reuse it rather than defining its own.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('applies_to');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
