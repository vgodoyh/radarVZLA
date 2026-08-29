<?php

use App\Http\Controllers\Admin\AccesoJusticiaDashboardController;
use App\Http\Controllers\Admin\AccesoJusticiaSyncController;
use App\Http\Controllers\Admin\OvfnDashboardController;
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
    ->name('organizations.jep');
Route::get('/acceso-justicia', [PublicDashboardController::class, 'accesoJusticia'])
    ->middleware('analytics.page:acceso_justicia,acceso-justicia')
    ->name('organizations.acceso-justicia');
Route::get('/fake-news', [PublicDashboardController::class, 'fakeNews'])
    ->middleware('analytics.page:ovfn,fake-news')
    ->name('organizations.fake-news');
Route::get('/observatorio-universidades', [PublicDashboardController::class, 'universidades'])
    ->name('organizations.universidades');

Route::get('/analytics/content/{publication}/{source}', AnalyticsContentRedirectController::class)
    ->whereNumber('publication')
    ->whereIn('source', ['home', 'organization'])
    ->name('analytics.content.redirect');

Route::get('/analytics/ovfn/content/{contentType}/{contentId}', AnalyticsOvfnContentRedirectController::class)
    ->whereIn('contentType', ['analysis', 'noti_fake'])
    ->whereNumber('contentId')
    ->middleware('signed')
    ->name('analytics.ovfn.content.redirect');

Route::get('/analytics/navigation/{organization}/{source}', AnalyticsNavigationRedirectController::class)
    ->whereIn('organization', ['acceso-justicia', 'ovfn'])
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
    Route::post('/admin/acceso-justicia/sync', AccesoJusticiaSyncController::class)
        ->middleware(['role:admin|super-admin', 'permission:sync acceso justicia dashboard'])
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
