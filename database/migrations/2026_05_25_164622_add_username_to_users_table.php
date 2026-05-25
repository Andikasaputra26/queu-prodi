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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });

        // Populate existing users' usernames using their email prefix
        \Illuminate\Support\Facades\DB::table('users')->get()->each(function ($user) {
            $username = explode('@', $user->email)[0];
            $original = $username;
            $count = 1;
            while (\Illuminate\Support\Facades\DB::table('users')->where('username', $username)->exists()) {
                $username = $original . $count++;
            }
            \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
