<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Highlights are ordered bullet points shown on an experience's page
     * (e.g. "Sigiriya Rock climb"). No soft deletes — like `places`, these
     * aren't editorially workflowed on their own.
     */
    public function up(): void
    {
        Schema::create('experience_highlights', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('experience_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experience_highlights');
    }
};
