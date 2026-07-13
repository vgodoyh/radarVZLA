<?php

use App\Http\Controllers\DenunciaController;
use App\Http\Controllers\EmisorController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\PalabrasClavesController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TipoDenunciaController;
use App\Http\Controllers\TipoEmisorController;
use App\Http\Controllers\TipoRedSocialController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;

//Route::view('/', 'welcome')->name('home');

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin', Dashboard::class)->name('dashboard');

    Route::redirect('/dashboard', '/admin');

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