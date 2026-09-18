<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Many-to-many tagging between experiences and the shared `categories`
     * table (already scoped via `categories.applies_to`, which anticipated
     * this reuse when the Destinations domain built it).
     */
    public function up(): void
    {
        Schema::create('experience_category', function (Blueprint $table): void {
            $table->foreignId('experience_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['experience_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experience_category');
    }
};
