<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SalesTargetController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WidgetController;

Route::redirect('/', '/dashboard');

Route::get('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['id', 'en'], true), 404);

    session(['locale' => $locale]);

    return back();
})->name('language.switch');

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/gm', [DashboardController::class, 'gm'])->name('dashboard.gm')->middleware('role:gm');
    Route::post('/dashboard/save-layout', [DashboardController::class, 'saveLayout'])->name('dashboard.saveLayout');

    // Bookings
    Route::resource('bookings', BookingController::class)->middleware('role:gm,manager,sales,operational');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index')->withoutMiddleware('role:gm,manager,sales,operational');

    // Clients
    // View (index/show) — gm, manager, sales, finance. Write (create/edit/dll) — HANYA sales.
    Route::resource('clients', ClientController::class)
        ->only(['index', 'show'])
        ->middleware('role:gm,manager,sales,finance');
    Route::resource('clients', ClientController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy'])
        ->middleware('role:sales');

    // Pipeline / Opportunities
    Route::get('/pipeline', [PipelineController::class, 'index'])->name('pipeline.index')->middleware('role:gm,manager,sales');
    Route::resource('opportunities', OpportunityController::class)->middleware('role:gm,manager,sales');
    Route::post('/opportunities/{opportunity}/advance-stage', [OpportunityController::class, 'advanceStage'])->name('opportunities.advance-stage')->middleware('role:gm,manager,sales');
    Route::post('/opportunities/{opportunity}/discount', [OpportunityController::class, 'storeDiscount'])->name('opportunities.discount')->middleware('role:sales,manager,gm');
    // Kanban-specific endpoints
    Route::patch('/opportunities/{opportunity}/move-stage', [OpportunityController::class, 'moveStage'])->name('opportunities.move-stage')->middleware('role:gm,manager,sales');
    Route::patch('/opportunities/{opportunity}/quick-update', [OpportunityController::class, 'quickUpdate'])->name('opportunities.quick-update')->middleware('role:gm,manager,sales');
    Route::get('/opportunities/{opportunity}/360', [OpportunityController::class, 'view360'])->name('opportunities.360')->middleware('role:gm,manager,sales');

    // Products
    Route::resource('products', ProductController::class)->middleware('role:gm,manager,finance');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index')->withoutMiddleware(['role:gm,manager,finance']);


    // Activity Logs
    Route::resource('activities', ActivityLogController::class)->except(['edit', 'update'])->middleware('role:gm,manager,sales');

    // KPI / Sales Targets
    Route::get('/kpi', [SalesTargetController::class, 'index'])->name('kpi.index')->middleware('role:gm,manager,sales');
    Route::post('/kpi/targets', [SalesTargetController::class, 'store'])->name('kpi.store')->middleware('role:gm,manager');

    // Subscriptions
    Route::resource('subscriptions', SubscriptionController::class)->middleware('role:gm,manager,finance');
    Route::post('/subscriptions/{subscription}/terminate', [SubscriptionController::class, 'terminate'])->name('subscriptions.terminate')->middleware('role:gm,finance');

    // Fleet (Operational)
    Route::resource('fleet', FleetController::class)->middleware('role:gm,manager,operational');

    // Finance
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index')->middleware('role:gm,manager,finance');
    Route::get('/finance/invoices/{invoice}', [FinanceController::class, 'show'])->name('invoices.show')->middleware('role:gm,manager,finance');

    // Maintenance
    Route::resource('maintenance', MaintenanceController::class)->middleware('role:gm,manager,operational');

    // Revenue (named route — referenced by views & tests)
    Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index')->middleware('role:gm,manager,finance');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index')->middleware('role:gm,manager');
    Route::get('/analytics/crosssell', [AnalyticsController::class, 'crosssell'])->name('analytics.crosssell')->middleware('role:gm,manager');
    Route::get('/analytics/pipeline', [AnalyticsController::class, 'pipeline'])->name('analytics.pipeline')->middleware('role:gm,manager');
    Route::get('/analytics/sales', [AnalyticsController::class, 'salesPerformance'])->name('analytics.sales')->middleware('role:gm,manager');

    // Sales Performance
    Route::get('/sales/{user}/performance', [SalesController::class, 'performance'])->name('sales.performance');

    // API Endpoints
    Route::prefix('api')->group(function () {
        Route::get('/revenue', [RevenueController::class, 'getRevenue']);
        Route::get('/revenue/per-sales', [RevenueController::class, 'getRevenuePerSales'])->middleware('role:gm,manager');
        Route::get('/products/search', [ProductController::class, 'apiSearch'])->name('api.products.search');
        Route::get('/search/global', [SearchController::class, 'global'])->name('search.global');
        Route::post('/widgets/save', [WidgetController::class, 'save'])->name('widgets.save');
        Route::post('/widgets/reset', [WidgetController::class, 'reset'])->name('widgets.reset');
        Route::get('/activities/upcoming', [ActivityLogController::class, 'apiUpcoming'])->middleware('role:gm,manager,sales');
        Route::get('/opportunities/by-client/{client}', [OpportunityController::class, 'byClient'])->middleware('role:gm,manager,sales');
        Route::get('/opportunities/{opportunity}/history', [OpportunityController::class, 'getHistory'])->middleware('role:gm,manager,sales');
    });
});

require __DIR__.'/auth.php';
