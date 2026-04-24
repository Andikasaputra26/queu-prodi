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
        Schema::table('queues', function (Blueprint $table) {
            // Drop the old unique constraint on queue_number
            $table->dropUnique(['queue_number']);
            
            // Add a new composite unique constraint on queue_number and queue_date
            // This allows the same queue_number to be used on different days
            $table->unique(['queue_number', 'queue_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropUnique(['queue_number', 'queue_date']);
            $table->unique('queue_number');
        });
    }
};
