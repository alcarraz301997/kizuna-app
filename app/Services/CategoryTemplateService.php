<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryTemplate;
use App\Models\Wedding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CategoryTemplateService
{
    public function create(Wedding $wedding, string $name, array $items): CategoryTemplate
    {
        return DB::transaction(function () use ($wedding, $name, $items): CategoryTemplate {
            $template = $wedding->categoryTemplates()->create(['name' => $name]);
            $itemIds = [];

            foreach ($this->orderedItems($items) as $index => $item) {
                $parentId = isset($item['parent_index']) ? ($itemIds[$item['parent_index']] ?? null) : null;
                $created = $template->items()->create([
                    'parent_id' => $parentId,
                    'name' => $item['name'],
                    'budget_limit' => $item['budget_limit'] ?? null,
                    'color' => $item['color'] ?? '#6366f1',
                    'sort_order' => $item['sort_order'] ?? $index,
                ]);
                $itemIds[$item['index']] = $created->id;
            }

            return $template->load('items');
        });
    }

    public function apply(Wedding $wedding, CategoryTemplate $template): Collection
    {
        abort_unless($template->wedding_id === $wedding->id, 404);

        return DB::transaction(function () use ($wedding, $template): Collection {
            $categories = collect();
            $categoryIds = [];

            foreach ($template->items()->orderBy('sort_order')->orderBy('id')->get() as $item) {
                $parentId = $item->parent_id ? ($categoryIds[$item->parent_id] ?? null) : null;
                $category = Category::firstOrCreate(
                    [
                        'wedding_id' => $wedding->id,
                        'name' => $item->name,
                        'parent_id' => $parentId,
                    ],
                    [
                        'user_id' => $wedding->owner_id,
                        'budget_limit' => $item->budget_limit,
                        'color' => $item->color,
                        'sort_order' => $item->sort_order,
                    ],
                );
                $categoryIds[$item->id] = $category->id;
                $categories->push($category);
            }

            return $categories;
        });
    }

    public function rollups(Wedding $wedding): Collection
    {
        $categories = $this->scopedCategories($wedding)->get();
        $totals = $this->groupedExpenseTotals($wedding);
        $children = $categories->groupBy('parent_id');
        $memo = [];

        $calculate = function (Category $category) use (&$calculate, &$memo, $children, $totals): array {
            if (isset($memo[$category->id])) {
                return $memo[$category->id];
            }

            $values = [
                'planned' => (float) ($totals[$category->id]['planned'] ?? 0),
                'contracted' => (float) ($totals[$category->id]['contracted'] ?? 0),
                'paid' => (float) ($totals[$category->id]['paid'] ?? 0),
            ];
            foreach ($children->get($category->id, collect()) as $child) {
                foreach ($calculate($child) as $key => $value) {
                    $values[$key] += $value;
                }
            }

            return $memo[$category->id] = $values;
        };

        return $categories->map(function (Category $category) use ($calculate): array {
            $values = $calculate($category);

            return [
                'id' => $category->id,
                'name' => $category->name,
                'parent_id' => $category->parent_id,
                'sort_order' => $category->sort_order,
                'planned' => $values['planned'],
                'contracted' => $values['contracted'],
                'paid' => $values['paid'],
            ];
        });
    }

    private function scopedCategories(Wedding $wedding)
    {
        return Category::query()->where('wedding_id', $wedding->id)->orderBy('sort_order')->orderBy('id');
    }

    private function groupedExpenseTotals(Wedding $wedding): Collection
    {
        return $wedding->expenses()
            ->select('category_id', 'status', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id', 'status')
            ->get()
            ->groupBy('category_id')
            ->map(fn (Collection $rows) => $rows->mapWithKeys(fn ($row) => [$row->status->value ?? $row->status => (float) $row->total]));
    }

    private function orderedItems(array $items): Collection
    {
        return collect($items)->values()->map(fn (array $item, int $index) => $item + ['index' => $index])
            ->sortBy(fn (array $item) => [$item['parent_index'] ?? -1, $item['sort_order'] ?? $item['index']])
            ->values();
    }
}
