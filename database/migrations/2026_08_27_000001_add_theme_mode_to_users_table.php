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
        if (! Schema::hasColumn('users', 'theme_mode')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('theme_mode')->default('general')->after('is_penalized');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'theme_mode')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('theme_mode');
            });
        }
    }
};
