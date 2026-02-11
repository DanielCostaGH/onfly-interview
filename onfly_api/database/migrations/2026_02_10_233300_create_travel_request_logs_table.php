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
        Schema::create('travel_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_request_id')->constrained('travel_requests')->cascadeOnDelete();
            $table->foreignId('changed_by_user_id')->constrained('users');
            $table->enum('from_status', ['requested', 'approved', 'canceled'])->nullable();
            $table->enum('to_status', ['requested', 'approved', 'canceled']);
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_request_logs');
    }
};
