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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('progress_percent',5,2)->default(0);
            $table->decimal('total_progress_percent',5,2)->nullable();
            $table->enum('status',['not_started','in_progress','hold','completed','cancelled'])->default('not_started');
            $table->enum('running_state',['UI/UX','Frontend','Backend','Flutter'])->nullable();
            $table->date('approximate_delivery_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_delivered')->default(false);
            $table->enum('post_delivery_state',['bug fixing','redesigning','deployment','new feature','all clear'])->nullable();
            $table->decimal('price',15,2)->nullable();
            $table->string('source_code')->nullable();
            $table->string('live_demo')->nullable();
            $table->enum('client_mood',['cool','hyper','happy','normal'])->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
