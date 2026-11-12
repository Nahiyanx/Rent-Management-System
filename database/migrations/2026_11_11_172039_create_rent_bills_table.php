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
        Schema::create('rent_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenancy_id')->constrained()->cascadeOnDelete();
            $table->string('billing_month', 7);
            $table->decimal('house_rent', 12, 2)->default(0);
            $table->decimal('electricity', 12, 2)->default(0);
            $table->decimal('gas', 12, 2)->default(0);
            $table->decimal('parking', 12, 2)->default(0);
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->decimal('other_charges', 12, 2)->default(0);
            $table->decimal('total_due', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['tenancy_id', 'billing_month']);
            $table->index('billing_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_bills');
    }
};
