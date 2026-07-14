<?php

use App\Http\Controllers\PqrController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConfigurationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('pqrs.index')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'active', 'verified'])->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('pqrs', PqrController::class)->except('show');

    Route::resource('usuarios', UserController::class)
        ->except('show')
        ->parameters(['usuarios' => 'user'])
        ->names('users')
        ->middleware('admin');

    Route::resource('auditoria', AuditController::class)
        ->only(['index', 'show'])
        ->parameters(['auditoria' => 'audit'])
        ->names('audits')
        ->middleware('admin');

    Route::middleware('admin')->prefix('reportes')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::post('/descargar', [ReportController::class, 'download'])->name('download');
        Route::post('/enviar', [ReportController::class, 'email'])->name('email');
    });

    Route::middleware('admin')->group(function () {
        Route::get('/configuracion', [ConfigurationController::class, 'index'])->name('configuration.index');
        Route::put('/configuracion', [ConfigurationController::class, 'update'])->name('configuration.update');
        Route::post('/configuracion/probar-correo', [ConfigurationController::class, 'testEmail'])->name('configuration.test-email');
        Route::resource('categorias', CategoryController::class)
            ->except(['index', 'show'])
            ->parameters(['categorias' => 'category'])
            ->names('categories');
    });
});

require __DIR__.'/auth.php';
