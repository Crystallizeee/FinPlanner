<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_transactions', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('receipt_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expense_transactions', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
