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
        Schema::create('tenancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flat_id')->constrained()->cascadeOnDelete();
            $table->decimal('house_rent', 12, 2)->default(0);
            $table->decimal('gas_charge', 12, 2)->default(0);
            $table->decimal('parking_charge', 12, 2)->default(0);
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            // 1 while the tenancy is active, NULL once it has ended. The unique
            // index allows many NULL rows per flat but only one active row.
            $table->unsignedTinyInteger('active_marker')->nullable();
            $table->timestamps();

            $table->unique(['flat_id', 'active_marker']);
            $table->index('active_marker');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenancies');
    }
};
