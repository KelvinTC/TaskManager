<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\UserManagementController;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');

    // User Preferences
    Route::post('/user/update-theme', [App\Http\Controllers\UserController::class, 'updateTheme'])->name('user.update-theme');

    // Tasks
    Route::resource('tasks', TaskController::class);
    Route::post('/tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::post('/tasks/{task}/reschedule', [TaskController::class, 'reschedule'])->name('tasks.reschedule');

    // Calendar
    Route::get('/calendar', [TaskController::class, 'calendar'])->name('calendar');
    Route::get('/tasks/json', [TaskController::class, 'json'])->name('tasks.json');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/worker-performance', [ReportController::class, 'workerPerformance'])->name('reports.worker-performance');
    Route::get('/reports/tasks-per-worker', [ReportController::class, 'tasksPerWorker'])->name('reports.tasks-per-worker');
    Route::get('/reports/time-based', [ReportController::class, 'timeBased'])->name('reports.time-based');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Admin - User Management (Super Admin & Admin)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/invite', [UserManagementController::class, 'inviteForm'])->name('users.invite.form');
        Route::post('/users/invite', [UserManagementController::class, 'invite'])->name('users.invite');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/invitations/{invitation}', [UserManagementController::class, 'destroy'])->name('invitations.destroy');
        Route::post('/users/{user}/promote', [UserManagementController::class, 'promoteToAdmin'])->name('users.promote');
        Route::post('/users/{user}/demote', [UserManagementController::class, 'demoteToEmployee'])->name('users.demote');
    });
});
