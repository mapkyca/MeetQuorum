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
        Schema::create('poll_slots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('poll_id');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('poll_id')
                ->references('id')
                ->on('polls')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poll_slots');
    }
};
