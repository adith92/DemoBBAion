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
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\VoucherController;
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
    Route::get('/dashboard/gm', [DashboardController::class, 'gm'])->name('dashboard.gm')->middleware('role:director,gm');

    // Bookings
    Route::resource('bookings', BookingController::class)->middleware('role:gm,sales,operational');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index')->withoutMiddleware('role:gm,sales,operational');

    // Clients
    Route::resource('clients', ClientController::class)->middleware('role:director,gm,manager,sales,finance');

    // Pipeline / Opportunities
    Route::get('/pipeline', [PipelineController::class, 'index'])->name('pipeline.index')->middleware('role:director,gm,manager,sales');
    Route::resource('opportunities', OpportunityController::class)->middleware('role:director,gm,manager,sales');
    Route::post('/opportunities/{opportunity}/advance-stage', [OpportunityController::class, 'advanceStage'])->name('opportunities.advance-stage')->middleware('role:director,gm,manager,sales');
    Route::post('/opportunities/{opportunity}/discount', [OpportunityController::class, 'storeDiscount'])->name('opportunities.discount')->middleware('role:sales,manager,gm');
    // Kanban-specific endpoints
    Route::patch('/opportunities/{opportunity}/move-stage', [OpportunityController::class, 'moveStage'])->name('opportunities.move-stage')->middleware('role:director,gm,manager,sales');
    Route::patch('/opportunities/{opportunity}/quick-update', [OpportunityController::class, 'quickUpdate'])->name('opportunities.quick-update')->middleware('role:director,gm,manager,sales');
    Route::get('/opportunities/{opportunity}/360', [OpportunityController::class, 'view360'])->name('opportunities.360')->middleware('role:director,gm,manager,sales');

    // Products
    Route::resource('products', ProductController::class)->middleware('role:director,gm,manager,finance');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index')->withoutMiddleware(['role:director,gm,manager,finance']);

    // Approvals
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index')->middleware('role:director,gm,manager,sales');
    Route::get('/approvals/{approval}', [ApprovalController::class, 'show'])->name('approvals.show')->middleware('role:director,gm,manager,sales');
    Route::post('/approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve')->middleware('role:director,gm,manager');
    Route::post('/approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject')->middleware('role:director,gm,manager');

    // Activity Logs
    Route::resource('activities', ActivityLogController::class)->except(['edit', 'update'])->middleware('role:director,gm,manager,sales');

    // KPI / Sales Targets
    Route::get('/kpi', [SalesTargetController::class, 'index'])->name('kpi.index')->middleware('role:director,gm,manager,sales');
    Route::post('/kpi/targets', [SalesTargetController::class, 'store'])->name('kpi.store')->middleware('role:director,gm,manager');

    // Subscriptions
    Route::resource('subscriptions', SubscriptionController::class)->middleware('role:director,gm,manager,finance');
    Route::post('/subscriptions/{subscription}/terminate', [SubscriptionController::class, 'terminate'])->name('subscriptions.terminate')->middleware('role:director,gm,finance');
    Route::post('/subscriptions/billing/run', [SubscriptionController::class, 'runBilling'])
        ->middleware('role:gm,finance,manager')
        ->name('subscriptions.billing.run');

    // Vouchers
    Route::resource('vouchers', VoucherController::class)->middleware('role:director,gm,manager,finance');
    Route::post('/vouchers/{voucher}/redeem', [VoucherController::class, 'redeem'])->name('vouchers.redeem')->middleware('role:director,gm,finance,operational');
    Route::post('/vouchers/{voucher}/expire', [VoucherController::class, 'expire'])->name('vouchers.expire')->middleware('role:director,gm,finance');
    Route::post('/vouchers/bulk', [VoucherController::class, 'bulkStore'])->name('vouchers.bulk');

    // Fleet (Operational)
    Route::resource('fleet', FleetController::class)->middleware('role:director,gm,manager,operational');
    Route::resource('vehicle-contracts', App\Http\Controllers\VehicleContractController::class)->middleware('role:director,gm,operational,manager');

    // Finance
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index')->middleware('role:director,gm,finance');
    Route::get('/finance/invoices/{invoice}', [FinanceController::class, 'show'])->name('invoices.show')->middleware('role:director,gm,finance');

    // Maintenance
    Route::resource('maintenance', MaintenanceController::class)->middleware('role:director,gm,manager,operational');

    // Revenue (named route — referenced by views & tests)
    Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index')->middleware('role:director,gm,manager,finance');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index')->middleware('role:director,gm,manager');
    Route::get('/analytics/crosssell', [AnalyticsController::class, 'crosssell'])->name('analytics.crosssell')->middleware('role:director,gm,manager');
    Route::get('/analytics/pipeline', [AnalyticsController::class, 'pipeline'])->name('analytics.pipeline')->middleware('role:director,gm,manager');
    Route::get('/analytics/sales', [AnalyticsController::class, 'salesPerformance'])->name('analytics.sales')->middleware('role:director,gm,manager');

    // Sales Performance
    Route::get('/sales/{user}/performance', [SalesController::class, 'performance'])->name('sales.performance');

    // API Endpoints
    Route::prefix('api')->group(function () {
        Route::get('/revenue', [RevenueController::class, 'getRevenue']);
        Route::get('/revenue/per-sales', [RevenueController::class, 'getRevenuePerSales'])->middleware('role:director,gm,manager');
        Route::get('/products/search', [ProductController::class, 'apiSearch'])->name('api.products.search');
        Route::get('/search/global', [SearchController::class, 'global'])->name('search.global');
        Route::post('/widgets/save', [WidgetController::class, 'save'])->name('widgets.save');
        Route::post('/widgets/reset', [WidgetController::class, 'reset'])->name('widgets.reset');
        Route::get('/activities/upcoming', [ActivityLogController::class, 'apiUpcoming'])->middleware('role:director,gm,manager,sales');
        Route::get('/opportunities/by-client/{client}', [OpportunityController::class, 'byClient'])->middleware('role:director,gm,manager,sales');
    });
});

require __DIR__.'/auth.php';
