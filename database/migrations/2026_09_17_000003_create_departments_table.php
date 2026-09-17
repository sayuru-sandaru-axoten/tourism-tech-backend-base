<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Departments are organisational units that staff members belong to.
     * `slug` is the URL-/code-friendly identifier (unique); `name` is the
     * human-readable display label (also unique — no two departments share a name).
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Replace the free-text `department` string on staff_profiles with a proper FK.
        Schema::table('staff_profiles', function (Blueprint $table): void {
            $table->foreignId('department_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->dropColumn('department');
        });
    }

    public function down(): void
    {
        Schema::table('staff_profiles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('department_id');
            $table->string('department')->nullable()->after('user_id');
        });

        Schema::dropIfExists('departments');
    }
};
