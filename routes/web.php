<?php

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PublicDashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TipoRedSocialController;
use App\Http\Controllers\UserController;
use App\Livewire\Admin\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicDashboardController::class, 'index'])
    ->name('dashboard.public');

Route::get('/home', [PublicDashboardController::class, 'index'])
    ->name('home');

Route::get('/home-v2', [PublicDashboardController::class, 'indexV2'])
    ->name('dashboard.public.v2');

Route::get('/home-v3', [PublicDashboardController::class, 'indexV3'])
    ->name('dashboard.public.v3');

Route::prefix('organizaciones')->name('organizations.')->group(function () {
    Route::get('/jep', [PublicDashboardController::class, 'jep'])->name('jep');
    Route::get('/acceso-justicia', [PublicDashboardController::class, 'accesoJusticia'])->name('acceso-justicia');
    Route::get('/fake-news', [PublicDashboardController::class, 'fakeNews'])->name('fake-news');
    Route::get('/universidades', [PublicDashboardController::class, 'universidades'])->name('universidades');
});

Route::get('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['es', 'en'], true), 404);

    session(['locale' => $locale]);

    return back();
})->name('language.switch');

Route::redirect('/entrar', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin', Dashboard::class)->name('dashboard');

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
