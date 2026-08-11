<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['categories', 'expenses', 'vendors', 'guests', 'tables'];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->foreignId('wedding_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
                $table->index(['wedding_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropForeign(['wedding_id']);
                $table->dropIndex(['wedding_id', 'user_id']);
                $table->dropColumn('wedding_id');
            });
        }
    }
};
