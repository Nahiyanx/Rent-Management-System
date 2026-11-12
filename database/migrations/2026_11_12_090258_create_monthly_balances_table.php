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
        Schema::create('monthly_balances', function (Blueprint $table) {
            $table->id();
            $table->string('month', 7)->unique();
            $table->decimal('cash_starting', 12, 2)->default(0);
            $table->decimal('bank_starting', 12, 2)->default(0);
            $table->boolean('cash_manual')->default(false);
            $table->boolean('bank_manual')->default(false);
            $table->decimal('cash_previous_closing', 12, 2)->nullable();
            $table->decimal('bank_previous_closing', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_balances');
    }
};
