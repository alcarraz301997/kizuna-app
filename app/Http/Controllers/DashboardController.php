<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the budget dashboard with aggregated totals.
     */
    public function __invoke(Request $request): Response
    {
        $categories = $request->user()->categories()
            ->with('expenses')
            ->orderBy('name')
            ->get();

        $categoryData = $categories->map(fn (Category $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'color' => $c->color,
            'budget_limit' => (float) $c->budget_limit,
            'spent' => $c->spent,
            'planned' => $c->planned,
            'remaining' => $c->remaining,
            'progress' => $c->progress,
        ]);

        $totalBudget = $categories->sum(fn (Category $c) => (float) $c->budget_limit);
        $totalSpent = $categories->sum(fn (Category $c) => $c->spent);
        $totalPlanned = $categories->sum(fn (Category $c) => $c->planned);
        $totalRemaining = $totalBudget - $totalSpent;

        return Inertia::render('Dashboard', [
            'categories' => $categoryData,
            'totals' => [
                'total_budget' => round($totalBudget, 2),
                'total_spent' => round($totalSpent, 2),
                'total_planned' => round($totalPlanned, 2),
                'total_remaining' => round($totalRemaining, 2),
            ],
        ]);
    }
}
