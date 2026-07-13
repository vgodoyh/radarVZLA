<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PublicDashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TipoRedSocialController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\XTimelineController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;

//Route::get('/dashboard-x-feeds', [XTimelineController::class, 'dashboardFeeds']);
//Route::view('/', 'x-feeds');

Route::get('/', [PublicDashboardController::class, 'index'])
    ->name('dashboard.public');

Route::get('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['es', 'en'], true), 404);

    session(['locale' => $locale]);

    return back();
})->name('language.switch');

Route::redirect('/entrar', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin', Dashboard::class)->name('dashboard');

    Route::redirect('/dashboard', '/admin');

    Route::get('/test-x-rapid/{username?}', [XTimelineController::class, 'test']);
    Route::get('/dashboard-x-feeds', [XTimelineController::class, 'dashboardFeeds']);

    Route::view('/x-feeds', 'x-feeds');

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

require __DIR__.'/settings.php';