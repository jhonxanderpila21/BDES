<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetTypeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AccessoryController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\OperationalRequirementController;
use App\Http\Controllers\AssetNeedController;
use App\Http\Controllers\AssetAccessoryController;
use App\Http\Controllers\ReportedAssetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssetDocumentController;
use App\Http\Controllers\AssetHistoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AssetDepreciationController;
use App\Http\Controllers\AssetSalvageController;
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
    Route::middleware(['role:administrator,barangay_captain'])->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password')->middleware('throttle:10,1');
        Route::get('/roles', function () {
            return view('admin.roles.index');
        })->name('roles.index');
    });

    // Asset Configuration
    Route::resource('asset-categories', AssetCategoryController::class);
    Route::resource('asset-types', AssetTypeController::class);
    Route::resource('locations', LocationController::class);

    // Asset Management
    Route::get('/assets/export', [AssetController::class, 'export'])->name('assets.export')->middleware('throttle:60,1');
    Route::get('/assets/search', [AssetController::class, 'search'])->name('assets.search');
    Route::post('/assets/{asset}/retire', [AssetController::class, 'retire'])->name('assets.retire');
    Route::post('/assets/{asset}/documents', [AssetDocumentController::class, 'store'])->name('assets.documents.store');
    Route::get('/documents/{document}/download', [AssetDocumentController::class, 'download'])->name('assets.documents.download');
    Route::delete('/documents/{document}', [AssetDocumentController::class, 'destroy'])->name('assets.documents.destroy');
    Route::resource('assets', AssetController::class);
    Route::resource('accessories', AccessoryController::class);
    Route::resource('attachments', AttachmentController::class);
    Route::resource('operational-requirements', OperationalRequirementController::class);
    Route::post('/operational-requirements/{operationalRequirement}/refill', [OperationalRequirementController::class, 'refill'])->name('operational-requirements.refill');
    Route::post('/operational-requirements/{operationalRequirement}/consume', [OperationalRequirementController::class, 'consume'])->name('operational-requirements.consume');

    // Asset Needs
    Route::resource('asset-needs', AssetNeedController::class)->except(['show', 'create', 'edit']);
    Route::post('asset-needs/{asset_need}/replenish', [AssetNeedController::class, 'replenish'])->name('asset-needs.replenish');

    // Asset Accessories
    Route::post('asset-accessories/{asset_accessory}/adjust-quantity', [AssetAccessoryController::class, 'adjustQuantity'])->name('asset-accessories.adjust-quantity');
    Route::resource('asset-accessories', AssetAccessoryController::class)->except(['create', 'show', 'edit']);

    // Operations (Deployments & Returns)
    Route::post('deployment/{deployment}/return', [DeploymentController::class, 'returnAsset'])->name('deployment.return');
    Route::resource('deployment', DeploymentController::class);
    Route::resource('returns', ReturnController::class);
    
    // Maintenance Schedules (Preventive Maintenance)
    Route::post('maintenance-schedules/{maintenance_schedule}/complete', [MaintenanceScheduleController::class, 'complete'])->name('maintenance-schedules.complete');
    Route::resource('maintenance-schedules', MaintenanceScheduleController::class)->except(['create', 'show', 'edit']);

    // Reported Assets
    Route::post('reported-assets/{reported_asset}/change-status', [ReportedAssetController::class, 'changeStatus'])->name('reported-assets.change-status');
    Route::resource('reported-assets', ReportedAssetController::class);

    // Maintenance & Inspection Hub
    Route::post('maintenance/{maintenance}/complete', [MaintenanceController::class, 'complete'])->name('maintenance.complete');
    Route::resource('maintenance', MaintenanceController::class);
    Route::resource('inspection', InspectionController::class);

    // Asset Financials & Valuation (Admin Only)
    Route::middleware(['role:administrator,barangay_captain'])->group(function () {
        Route::get('/financials/depreciation/export', [AssetDepreciationController::class, 'export'])->name('financials.depreciation.export')->middleware('throttle:60,1');
        Route::post('/financials/depreciation/{asset}', [AssetDepreciationController::class, 'update'])->name('financials.depreciation.update');
        Route::get('/financials/depreciation', [AssetDepreciationController::class, 'index'])->name('financials.depreciation');

        Route::get('/financials/salvage/export', [AssetSalvageController::class, 'export'])->name('financials.salvage.export')->middleware('throttle:60,1');
        Route::post('/financials/salvage/{asset}', [AssetSalvageController::class, 'update'])->name('financials.salvage.update');
        Route::get('/financials/salvage', [AssetSalvageController::class, 'index'])->name('financials.salvage');

        // Reports Center (Admin Only)
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export')->middleware('throttle:60,1');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // Asset History & Audit Trail (Accessible to Members & Admins)
    Route::get('/asset-history/export', [AssetHistoryController::class, 'export'])->name('asset-history.export')->middleware('throttle:60,1');
    Route::get('/asset-history', [AssetHistoryController::class, 'index'])->name('asset-history.index');

    // Proof-of-cost receipts (shared by every costed module)
    Route::get('/receipts/{receipt}', [\App\Http\Controllers\ReceiptController::class, 'show'])->name('receipts.show');
    Route::delete('/receipts/{receipt}', [\App\Http\Controllers\ReceiptController::class, 'destroy'])->name('receipts.destroy');

    // Profile (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Authentication routes (Breeze)
require __DIR__.'/auth.php';
