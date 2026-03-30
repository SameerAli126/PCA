<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MapLayerService;
use Illuminate\Http\JsonResponse;

class MapLayerController extends Controller
{
    public function __invoke(MapLayerService $mapLayerService): JsonResponse
    {
        return response()->json($mapLayerService->layers());
    }
}
