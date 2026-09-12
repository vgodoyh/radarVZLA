<?php

use App\Http\Controllers\Admin\AccesoJusticiaDashboardController;
use App\Http\Controllers\Admin\AccesoJusticiaSyncController;
use App\Http\Controllers\Admin\OvfnDashboardController;
use App\Http\Controllers\Admin\ObuDashboardController;
use App\Http\Controllers\Admin\JepDashboardController;
use App\Http\Controllers\AnalyticsContentRedirectController;
use App\Http\Controllers\AnalyticsNavigationRedirectController;
use App\Http\Controllers\AnalyticsOvfnContentRedirectController;
use App\Http\Middleware\RedirectAccessJusticeUserFromAdmin;
use App\Http\Middleware\UpdateUserLastActivity;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PublicDashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TipoRedSocialController;
use App\Http\Controllers\UserController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicDashboardController::class, 'index'])
    ->middleware('analytics.page:pulso_vzla,home')
    ->name('dashboard.public');

Route::get('/home', [PublicDashboardController::class, 'index'])
    ->name('home');

Route::get('/justicia-encuentro-perdon', [PublicDashboardController::class, 'jep'])
    ->middleware('analytics.page:jep,justicia-encuentro-perdon')
    ->name('organizations.jep');
Route::get('/acceso-justicia', [PublicDashboardController::class, 'accesoJusticia'])
    ->middleware('analytics.page:acceso_justicia,acceso-justicia')
    ->name('organizations.acceso-justicia');
Route::get('/fake-news', [PublicDashboardController::class, 'fakeNews'])
    ->middleware('analytics.page:ovfn,fake-news')
    ->name('organizations.fake-news');
Route::get('/observatorio-universidades', [PublicDashboardController::class, 'universidades'])
    ->middleware('analytics.page:universidades,observatorio-universidades')
    ->name('organizations.universidades');

Route::get('/analytics/content/{publication}/{source}', AnalyticsContentRedirectController::class)
    ->whereNumber('publication')
    ->whereIn('source', ['home', 'organization'])
    ->name('analytics.content.redirect');

Route::get('/analytics/obu/content/{publication}/{source}', AnalyticsContentRedirectController::class)
    ->whereNumber('publication')
    ->whereIn('source', ['home', 'organization'])
    ->name('analytics.obu.content.redirect');

Route::get('/analytics/jep/content/{publication}/{source}', AnalyticsContentRedirectController::class)
    ->whereNumber('publication')
    ->whereIn('source', ['home', 'organization'])
    ->name('analytics.jep.content.redirect');

Route::get('/analytics/jep/alert/{publication}/{source}', AnalyticsContentRedirectController::class)
    ->whereNumber('publication')
    ->whereIn('source', ['home', 'organization'])
    ->name('analytics.jep.alert.redirect');

Route::get('/analytics/jep/featured/{publication}/{type}/{source}', AnalyticsContentRedirectController::class)
    ->whereNumber('publication')
    ->whereIn('type', ['featured_instagram', 'featured_x', 'featured_read_more'])
    ->whereIn('source', ['home', 'organization'])
    ->name('analytics.jep.featured.redirect');

Route::get('/analytics/ovfn/content/{contentType}/{contentId}', AnalyticsOvfnContentRedirectController::class)
    ->whereIn('contentType', ['analysis', 'noti_fake'])
    ->whereNumber('contentId')
    ->middleware('signed')
    ->name('analytics.ovfn.content.redirect');

Route::get('/analytics/navigation/{organization}/{source}', AnalyticsNavigationRedirectController::class)
    ->whereIn('organization', ['acceso-justicia', 'jep', 'ovfn', 'universidades'])
    ->whereIn('source', ['home'])
    ->name('analytics.navigation.redirect');

Route::get('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['es', 'en'], true), 404);

    session(['locale' => $locale]);

    return back();
})->name('language.switch');

Route::redirect('/entrar', '/login');

Route::middleware(['auth', 'verified', UpdateUserLastActivity::class])->group(function () {
    Route::get('/admin', Dashboard::class)
        ->middleware(RedirectAccessJusticeUserFromAdmin::class)
        ->name('dashboard');
    Route::livewire('/admin/profile', Profile::class)
        ->name('admin.profile.edit');

    Route::get('/admin/acceso-justicia', AccesoJusticiaDashboardController::class)
        ->middleware('permission:view acceso justicia dashboard')
        ->name('admin.acceso-justicia.index');
    Route::get('/admin/ovfn', OvfnDashboardController::class)
        ->middleware('permission:view ovfn dashboard')
        ->name('admin.ovfn.index');
    Route::patch('/admin/ovfn/total-verifications', [OvfnDashboardController::class, 'updateTotalVerifications'])
        ->middleware('permission:edit ovfn metrics')
        ->name('admin.ovfn.total-verifications.update');
    Route::patch('/admin/ovfn/platform-distribution', [OvfnDashboardController::class, 'updatePlatformDistribution'])
        ->middleware('permission:edit ovfn metrics')
        ->name('admin.ovfn.platform-distribution.update');
    Route::get('/admin/obu', ObuDashboardController::class)
        ->middleware('permission:view obu dashboard')
        ->name('admin.obu.index');
    Route::get('/admin/jep', JepDashboardController::class)
        ->middleware('permission:view jep dashboard')
        ->name('admin.jep.index');
    Route::patch('/admin/jep/metrics', [JepDashboardController::class, 'updateMetrics'])
        ->middleware('permission:edit jep metrics')
        ->name('admin.jep.metrics.update');
    Route::patch('/admin/jep/main-metrics', [JepDashboardController::class, 'updateMainMetrics'])
        ->middleware('permission:edit jep metrics')
        ->name('admin.jep.main-metrics.update');
    Route::patch('/admin/jep/featured-indicator', [JepDashboardController::class, 'updateFeaturedIndicator'])
        ->middleware('permission:edit jep metrics')
        ->name('admin.jep.featured-indicator.update');
    Route::patch('/admin/jep/death-custody-distribution', [JepDashboardController::class, 'updateDeathCustodyDistribution'])
        ->middleware('permission:edit jep metrics')
        ->name('admin.jep.death-custody.update');
    Route::patch('/admin/jep/monthly-alert', [JepDashboardController::class, 'updateMonthlyAlert'])
        ->middleware('permission:edit jep metrics')
        ->name('admin.jep.monthly-alert.update');
    Route::patch('/admin/jep/indicators', [JepDashboardController::class, 'updateIndicators'])
        ->middleware('permission:edit jep metrics')
        ->name('admin.jep.indicators.update');
    Route::patch('/admin/jep/vulnerable-groups', [JepDashboardController::class, 'updateVulnerableGroups'])
        ->middleware('permission:edit jep metrics')
        ->name('admin.jep.vulnerable-groups.update');
    Route::patch('/admin/jep/detention-centers', [JepDashboardController::class, 'updateDetentionCenters'])
        ->middleware('permission:edit jep metrics')
        ->name('admin.jep.detention-centers.update');
    Route::post('/admin/jep/monthly-alert/fetch-x-post', [JepDashboardController::class, 'fetchMonthlyAlertPost'])
        ->middleware('permission:edit jep metrics')
        ->name('admin.jep.monthly-alert.fetch-x-post');
    Route::patch('/admin/obu/metrics', [ObuDashboardController::class, 'updateMetrics'])
        ->middleware('permission:edit obu metrics')
        ->name('admin.obu.metrics.update');
    Route::post('/admin/obu/monthly-note', [ObuDashboardController::class, 'storeMonthlyNote'])
        ->middleware('permission:edit obu metrics')
        ->name('admin.obu.monthly-note.store');
    Route::post('/admin/obu/bimonthly-alert', [ObuDashboardController::class, 'storeBimonthlyAlert'])
        ->middleware('permission:edit obu metrics')
        ->name('admin.obu.bimonthly-alert.store');
    Route::patch('/admin/obu/monitoring-period', [ObuDashboardController::class, 'updateMonitoringPeriod'])
        ->middleware('permission:edit obu metrics')
        ->name('admin.obu.monitoring-period.update');
    Route::patch('/admin/obu/dataset', [ObuDashboardController::class, 'updateDataset'])
        ->middleware('permission:edit obu metrics')
        ->name('admin.obu.dataset.update');
    Route::post('/admin/acceso-justicia/sync', AccesoJusticiaSyncController::class)
        ->middleware('permission:sync acceso justicia dashboard')
        ->name('admin.acceso-justicia.sync');
    Route::get('/admin/acceso-justicia/sync/status', [AccesoJusticiaSyncController::class, 'status'])
        ->middleware('permission:view acceso justicia dashboard')
        ->name('admin.acceso-justicia.sync.status');

    Route::redirect('/dashboard', '/admin');

    Route::middleware('role:admin|super-admin')->group(function () {

        Route::prefix('tipo_red_social')->name('tipo_red_social.')->group(function () {
            Route::get('papelera', [TipoRedSocialController::class, 'papelera'])->name('papelera');
            Route::put('{id}/restore', [TipoRedSocialController::class, 'restore'])->name('restore');
            Route::put('restore-all', [TipoRedSocialController::class, 'restoreAll'])->name('restoreAll');
        });

        Route::resource('tipo_red_social', TipoRedSocialController::class)
            ->except(['show'])
            ->parameters(['tipo_red_social' => 'tipo_red_social']);

        Route::resource('user', UserController::class);

        Route::resource('permission', PermissionController::class);
        Route::resource('role', RoleController::class);

    });

});

require __DIR__.'/settings.php';
