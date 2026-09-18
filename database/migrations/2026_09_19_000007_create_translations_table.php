<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Translations are polymorphic and shared platform-wide, localizing one
     * field on any translatable record (e.g. a destination's "description"
     * in "fr"). The unique index doubles as the upsert key.
     */
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('translatable_type');
            $table->unsignedBigInteger('translatable_id');
            $table->string('locale', 10);
            $table->string('field_key');
            $table->text('value');
            $table->timestamps();

            $table->unique(['translatable_type', 'translatable_id', 'locale', 'field_key'], 'translations_unique_target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
