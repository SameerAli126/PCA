<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\DatasetController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicMapController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/', [PublicMapController::class, 'index'])->name('atlas.index');
Route::get('/facilities/{facility:slug}', [PublicMapController::class, 'show'])->name('facilities.show');

Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:super_admin|analyst|data_manager'])
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::get('/datasets', [DatasetController::class, 'index'])->name('datasets.index');
        Route::post('/datasets', [DatasetController::class, 'store'])->name('datasets.store');

        Route::get('/imports', [ImportController::class, 'index'])->name('imports.index');
        Route::post('/imports', [ImportController::class, 'store'])->name('imports.store');
        Route::get('/imports/{importRun}', [ImportController::class, 'show'])->name('imports.show');
        Route::post('/imports/{importRun}/validate', [ImportController::class, 'validateImport'])->name('imports.validate');
        Route::post('/imports/{importRun}/publish', [ImportController::class, 'publish'])->name('imports.publish');
    });

require __DIR__.'/auth.php';
