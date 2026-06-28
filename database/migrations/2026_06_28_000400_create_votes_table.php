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
        Schema::create('votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('voter_id');
            $table->uuid('slot_id');
            $table->enum('response', ['yes', 'no', 'if_needed']);
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->unique(['voter_id', 'slot_id']);

            $table->foreign('voter_id')
                ->references('id')
                ->on('voters')
                ->cascadeOnDelete();

            $table->foreign('slot_id')
                ->references('id')
                ->on('poll_slots')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
