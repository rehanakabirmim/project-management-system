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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('profile_name');
            $table->enum('status',['not_started','in_progress','hold','completed','cancelled'])->default('not_started');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('deadline')->nullable();
            $table->decimal('remaining_hours',8,2)->nullable();
            $table->string('order_sheet_link')->nullable();
            $table->decimal('total_amount',15,2)->nullable();
            $table->enum('running_state',['UI/UX','Frontend','Backend','Flutter'])->nullable();
            $table->date('delivery_date')->nullable();
            $table->enum('is_delivered',['delivered','ongoing','requested'])->default('ongoing');
            $table->enum('post_delivery_state',['bug fixing','redesigning','deployment','new feature','all clear'])->nullable();
            $table->enum('client_mood',['cool','hyper','happy','normal'])->nullable();
            $table->text('issue')->nullable();
            $table->enum('color_code',['light_black','light_violet','green','yellow'])->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
