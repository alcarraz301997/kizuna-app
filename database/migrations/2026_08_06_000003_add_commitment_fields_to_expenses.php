<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('planned_amount', 10, 2)->nullable()->after('amount');
            $table->decimal('contracted_amount', 10, 2)->nullable()->after('planned_amount');
            $table->date('due_date')->nullable()->after('paid_date');
            $table->index(['wedding_id', 'status']);
            $table->index(['wedding_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['wedding_id', 'status']);
            $table->dropIndex(['wedding_id', 'due_date']);
            $table->dropColumn(['planned_amount', 'contracted_amount', 'due_date']);
        });
    }
};
