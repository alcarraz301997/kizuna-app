<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('wedding_id')->constrained('categories')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->after('parent_id');
            $table->index(['wedding_id', 'parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['wedding_id', 'parent_id', 'sort_order']);
            $table->dropColumn(['parent_id', 'sort_order']);
        });
    }
};
