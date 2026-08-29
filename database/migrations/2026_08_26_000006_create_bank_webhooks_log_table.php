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
        Schema::create('bank_webhooks_log', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->default('API_MUTASI');
            $table->string('reference_id')->nullable()->index();
            $table->text('payload');
            $table->string('signature')->nullable();
            $table->enum('status', ['received', 'processed', 'failed'])->default('received');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_webhooks_log');
    }
};
