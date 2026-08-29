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
        Schema::create('budget_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "August 2026 Discipline Cycle"
            $table->enum('period_type', ['monthly', 'weekly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('planned_budget', 15, 2); // e.g. 5,000,000.00 IDR
            $table->decimal('spent_amount', 15, 2)->default(0.00);
            $table->integer('hp_level')->default(100);
            $table->enum('status', ['active', 'completed', 'critical'])->default('active');
            $table->decimal('surplus_amount', 15, 2)->default(0.00);
            $table->integer('surplus_converted_ap')->default(0);
            $table->boolean('is_evaluated')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_cycles');
    }
};
