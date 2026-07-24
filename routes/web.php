<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PqrController;
use App\Http\Controllers\SettingsController;
use App\Models\PqrAttachment;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\PqrReplyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PqrInternalCommentController;
use App\Http\Controllers\PqrQuickActionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ComplementaryController;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'pqrs.index' : 'login');
});

Route::middleware('guest')->group(function () {
    Route::get('/iniciar-sesion', [AuthController::class, 'create'])->name('login');
    Route::post('/iniciar-sesion', [AuthController::class, 'store'])->name('login.store');
    Route::get('/recuperar-contrasena', [AuthController::class, 'forgotPassword'])->name('password.request');
    Route::post('/recuperar-contrasena', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::post('/restablecer-contrasena', [AuthController::class, 'updatePassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/cerrar-sesion', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/configuracion', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/configuracion', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/adjuntos/{attachment}', function (PqrAttachment $attachment) {
        abort_unless(request()->user()->can('view', $attachment->pqr), 403);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    })->name('attachments.download');
    Route::resource('pqrs', PqrController::class);
    Route::post('/pqrs/{pqr}/respuestas', [PqrReplyController::class, 'store'])->name('pqrs.replies.store');
    Route::post('/pqrs/{pqr}/comentarios-internos', [PqrInternalCommentController::class, 'store'])->name('pqrs.comments.store');
    Route::patch('/pqrs/{pqr}/accion-rapida', [PqrQuickActionController::class, 'update'])->name('pqrs.quick-update');
    Route::get('/pqrs/{pqr}/respuestas/{reply}/archivos/{file}', [PqrReplyController::class, 'download'])->name('pqrs.replies.download');
    Route::get('/notificaciones', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notificaciones/leer-todas', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notificaciones/{notification}', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/usuarios', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/usuarios', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/usuarios/{user}/editar', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/usuarios/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/usuarios/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::patch('/usuarios/{user}/rol', [UserManagementController::class, 'updateRole'])->name('users.role.update');
    Route::get('/informes/pqrs.csv', [ReportController::class, 'csv'])->name('reports.csv');
    Route::get('/informes/pqrs.xlsx', [ReportController::class, 'xlsx'])->name('reports.xlsx');
    Route::get('/informes/pqrs.pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('/gestion/carga', [ComplementaryController::class, 'workload'])->name('management.workload');
    Route::get('/gestion/herramientas', [ComplementaryController::class, 'tools'])->name('management.tools');
    Route::post('/gestion/plantillas', [ComplementaryController::class, 'template'])->name('management.templates.store');
    Route::post('/gestion/etiquetas', [ComplementaryController::class, 'tag'])->name('management.tags.store');
    Route::post('/gestion/reglas', [ComplementaryController::class, 'rule'])->name('management.rules.store');
    Route::patch('/pqrs/{pqr}/etiquetas', [ComplementaryController::class, 'syncTags'])->name('pqrs.tags.sync');
    Route::post('/pqrs/{pqr}/satisfaccion', [ComplementaryController::class, 'survey'])->name('pqrs.survey.store');
    Route::get('/residentes', [ComplementaryController::class, 'residents'])->name('management.residents');
    Route::patch('/residentes/{user}/unidad', [ComplementaryController::class, 'resident'])->name('management.residents.update');
    Route::get('/auditoria', [ComplementaryController::class, 'audit'])->name('management.audit');
});
