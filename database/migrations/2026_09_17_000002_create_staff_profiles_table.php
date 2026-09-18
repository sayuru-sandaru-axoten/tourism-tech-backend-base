<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One profile per user (`user_id` unique). `employee_code` is also unique —
     * no two staff members share the same code. All columns except the FK are
     * nullable so a bare staff User row works immediately after creation, and
     * profile details can be filled in later.
     */
    public function up(): void
    {
        Schema::create('staff_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('department')->nullable();
            $table->string('employee_code')->unique()->nullable();
            $table->date('hire_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};
