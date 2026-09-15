<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A plain string, not a DB-native enum column — validity is enforced by
     * App\Domains\Identity\Enums\UserStatus via the model cast, not the
     * database, so adding a status later is a PHP change, not a migration.
     *
     * The `default('active')` here is a safety net for raw SQL/DB::table()
     * inserts only. Eloquent never syncs a DB-level default back into an
     * in-memory model after create(), so User::$attributes carries the real,
     * authoritative default — see the comment there.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('status')->default('active')->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
