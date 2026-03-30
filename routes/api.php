<?php

use App\Http\Controllers\Api\AdministrativeAreaController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\MapLayerController;
use Illuminate\Support\Facades\Route;

Route::get('/map/layers', MapLayerController::class)->name('api.map.layers');
Route::get('/facilities', [FacilityController::class, 'index'])->name('api.facilities.index');
Route::get('/facilities/{facility:slug}', [FacilityController::class, 'show'])->name('api.facilities.show');
Route::get('/areas/{administrativeArea:slug}', [AdministrativeAreaController::class, 'show'])->name('api.areas.show');
Route::get('/analytics/summary', AnalyticsController::class)->name('api.analytics.summary');
