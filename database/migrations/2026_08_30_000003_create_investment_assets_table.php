<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('investment_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('asset_name');
            $table->string('asset_type'); // E.g., Saham, Reksa Dana, Emas, Crypto, Obligasi, Properti
            $table->decimal('quantity', 15, 4)->default(1);
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('current_price', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_assets');
    }
};
