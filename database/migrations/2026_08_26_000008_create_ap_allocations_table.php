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
        Schema::create('ap_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quest_pool_id')->constrained()->cascadeOnDelete();
            $table->foreignId('budget_cycle_id')->constrained()->cascadeOnDelete();
            $table->integer('ap_spent');
            $table->decimal('converted_amount', 15, 2);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'quest_pool_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ap_allocations');
    }
};
