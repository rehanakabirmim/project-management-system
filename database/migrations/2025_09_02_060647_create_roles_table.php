<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
        $table->id();
        $table->string('role_name')->unique(); // Spatie-required
        $table->string('guard_name')->default('web'); // Spatie-required
        $table->integer('level')->default(1); // Custom field: hierarchy level
        $table->foreignId('parent_id')->nullable()->constrained('roles')->nullOnDelete(); // hierarchy parent
        $table->boolean('can_manage_projects')->default(false); // Custom permission flag
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
