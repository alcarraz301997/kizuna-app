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
        Schema::create('expense_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('split_type');
            $table->string('person_a_label')->default('Él');
            $table->decimal('person_a_amount', 10, 2);
            $table->string('person_b_label')->default('Ella');
            $table->decimal('person_b_amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_splits');
    }
};
