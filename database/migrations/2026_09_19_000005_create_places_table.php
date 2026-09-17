<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Places are points of interest inside a destination (landmarks,
     * viewpoints, temples, beaches, trails). No soft deletes here —
     * unlike destinations, places aren't editorially workflowed.
     */
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('place_type');
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
