<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('queue_number', 10)->unique();
            $table->string('patient_name');
            $table->string('phone', 20)->nullable();
            $table->foreignId('service_id')->constrained('queue_services');
            $table->unsignedBigInteger('counter_id')->nullable(); // FK added after queue_counters is created
            $table->enum('status', ['waiting', 'calling', 'serving', 'done', 'skip'])->default('waiting');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('done_at')->nullable();
            $table->integer('sequence')->default(0);
            $table->date('queue_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
