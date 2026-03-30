<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeArea;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request, AnalyticsService $analyticsService): JsonResponse
    {
        $area = null;

        if ($request->filled('area')) {
            $area = AdministrativeArea::query()->where('slug', $request->string('area')->toString())->first();
        }

        return response()->json($analyticsService->summary($area));
    }
}
