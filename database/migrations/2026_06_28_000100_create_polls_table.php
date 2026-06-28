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
        Schema::create('polls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('permalink_token', 32)->unique();
            $table->uuid('creator_user_id')->nullable();
            $table->string('creator_name', 255)->nullable();
            $table->string('creator_email', 255)->nullable();
            $table->string('mgmt_token', 64)->nullable();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('meeting_link', 500)->nullable();
            $table->string('creator_tz', 64);
            $table->enum('slot_granularity', ['30min', '1hour', 'half_day', 'full_day']);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();

            $table->foreign('creator_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polls');
    }
};
