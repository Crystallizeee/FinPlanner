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
        Schema::create('quest_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "IDX Stock Averaging Down", "CB150R Maintenance"
            $table->string('slug');
            $table->enum('category', ['investment', 'vehicle', 'emergency', 'hobby'])->default('investment');
            $table->decimal('target_amount', 15, 2);
            $table->integer('allocated_ap')->default(0);
            $table->decimal('current_amount', 15, 2)->default(0.00);
            $table->string('icon')->default('shield-check');
            $table->timestamps();

            $table->index(['user_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quest_pools');
    }
};
