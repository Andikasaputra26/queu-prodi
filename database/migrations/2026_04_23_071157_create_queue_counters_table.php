<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_counters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('officer_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('current_queue_id')->nullable();
            $table->timestamps();
        });

        // Add FK from queues.counter_id -> queue_counters.id
        Schema::table('queues', function (Blueprint $table) {
            $table->foreign('counter_id')->references('id')->on('queue_counters')->nullOnDelete();
        });

        // Add FK from queue_counters.current_queue_id -> queues.id
        Schema::table('queue_counters', function (Blueprint $table) {
            $table->foreign('current_queue_id')->references('id')->on('queues')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropForeign(['counter_id']);
        });
        Schema::table('queue_counters', function (Blueprint $table) {
            $table->dropForeign(['current_queue_id']);
        });
        Schema::dropIfExists('queue_counters');
    }
};
