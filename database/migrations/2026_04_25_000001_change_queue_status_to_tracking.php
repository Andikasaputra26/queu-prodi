<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            // Kita ubah ENUM status agar sesuai dengan alur pelacakan surat
            // Status: pending, admin, prodi, ready, done, rejected
            $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->enum('status', ['waiting', 'calling', 'serving', 'done', 'skip'])->default('waiting')->change();
        });
    }
};
