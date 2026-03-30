<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use App\Models\ImportRun;
use App\Services\AnalyticsService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(AnalyticsService $analyticsService): View
    {
        return view('admin.dashboard', [
            'summary' => $analyticsService->summary(),
            'datasets' => Dataset::query()->with('versions')->latest()->take(5)->get(),
            'imports' => ImportRun::query()->with('datasetVersion.dataset', 'initiatedBy')->latest()->take(8)->get(),
        ]);
    }
}
