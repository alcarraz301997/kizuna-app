<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['wedding_id', 'name']);
        });

        Schema::create('category_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('category_template_items')->nullOnDelete();
            $table->string('name');
            $table->decimal('budget_limit', 10, 2)->nullable();
            $table->string('color', 7)->default('#6366f1');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            // Added explicit custom index name to fit within MySQL's 64-character limit
            $table->index(
                ['category_template_id', 'parent_id', 'sort_order'],
                'cat_tpl_items_parent_sort_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_template_items');
        Schema::dropIfExists('category_templates');
    }
};
