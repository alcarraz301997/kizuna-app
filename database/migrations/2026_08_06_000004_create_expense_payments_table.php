<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('paid_on')->nullable();
            $table->string('kind', 20);
            $table->string('origin', 30)->default('manual');
            $table->string('legacy_key')->nullable()->unique();
            $table->timestamps();
            $table->index(['expense_id', 'paid_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_payments');
    }
};
