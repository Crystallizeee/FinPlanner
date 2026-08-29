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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_number')->nullable()->index();
            $table->string('merchant_name');
            $table->decimal('total_amount', 15, 2);
            $table->dateTime('transaction_date');
            $table->string('image_path');
            $table->text('ocr_raw_text')->nullable();
            $table->enum('ocr_status', ['pending', 'processed', 'failed', 'manually_corrected'])->default('pending');
            $table->decimal('confidence_score', 5, 2)->default(0.00);
            $table->timestamps();

            $table->index(['user_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
