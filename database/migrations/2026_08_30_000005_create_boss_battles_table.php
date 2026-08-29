<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boss_battles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('boss_name');
            $table->integer('max_hp')->default(1000);
            $table->integer('current_hp')->default(1000);
            $table->integer('reward_xp')->default(1500);
            $table->string('status')->default('active'); // active, defeated, escaped
            $table->string('month_year'); // e.g. 2026-08
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boss_battles');
    }
};
