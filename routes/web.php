<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetTypeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AccessoryController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\IncidentReportController;
use App\Http\Controllers\OperationalRequirementController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Admin routes - requires authentication
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Foundation Modules (Admin only)
    Route::middleware(['role:administrator'])->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('/roles', function () {
            return view('admin.roles.index');
        })->name('roles.index');
    });

    // Asset Configuration
    Route::resource('asset-categories', AssetCategoryController::class);
    Route::resource('asset-types', AssetTypeController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('suppliers', SupplierController::class);

    // Asset Management
    Route::get('/assets/search', [AssetController::class, 'search'])->name('assets.search');
    Route::resource('assets', AssetController::class);
    Route::resource('accessories', AccessoryController::class);
    Route::resource('attachments', AttachmentController::class);
    Route::resource('operational-requirements', OperationalRequirementController::class);
    Route::post('/operational-requirements/{operationalRequirement}/refill', [OperationalRequirementController::class, 'refill'])->name('operational-requirements.refill');
    Route::post('/operational-requirements/{operationalRequirement}/consume', [OperationalRequirementController::class, 'consume'])->name('operational-requirements.consume');

    // Operations
    Route::resource('deployment', DeploymentController::class);
    Route::resource('returns', ReturnController::class);
    Route::resource('maintenance', MaintenanceController::class);
    Route::resource('inspection', InspectionController::class);
    Route::resource('incident-reports', IncidentReportController::class);

    // Monitoring
    Route::get('/asset-history', function () {
        return view('admin.asset-history.index');
    })->name('asset-history.index');

    Route::get('/reports', function () {
        return view('admin.reports.index');
    })->name('reports.index');

    // Profile (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Authentication routes (Breeze)
require __DIR__.'/auth.php';
