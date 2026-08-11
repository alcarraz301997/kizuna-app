<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Services\BudgetMetrics;
use App\Services\WeddingContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class WeddingMetricsController extends Controller
{
    public function forecast(Request $request, Wedding $wedding, WeddingContext $context, BudgetMetrics $metrics): Response|JsonResponse
    {
        $context->authorize($request, $wedding);
        $props = ['forecast' => $metrics->forecast($wedding)];

        if ($request->expectsJson()) {
            return response()->json($props);
        }

        return Inertia::render('Weddings/Forecast', $props);
    }

    public function variance(Request $request, Wedding $wedding, WeddingContext $context, BudgetMetrics $metrics): Response|JsonResponse
    {
        $context->authorize($request, $wedding);
        $props = ['categories' => $metrics->variance($wedding)];

        if ($request->expectsJson()) {
            return response()->json($props);
        }

        return Inertia::render('Weddings/Variance', $props);
    }
}
