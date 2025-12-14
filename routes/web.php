<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Lightweight health endpoint for platform health checks (no auth, no DB)
Route::get('/health', function () {
    return response('OK', 200);
});

// Serve favicon explicitly via PHP to avoid proxy/static misrouting causing 502
Route::get('/favicon.ico', function () {
    $path = public_path('favicon.ico');
    if (! file_exists($path)) {
        return response('', 204);
    }
    return response()->file($path, [
        'Cache-Control' => 'public, max-age=2592000', // 30 days
    ]);
});

// On-demand diagnostics endpoint (disabled unless DIAG_TOKEN is set in env)
if (!empty(env('DIAG_TOKEN'))) {
    Route::get('/diag', function (Request $request) {
        if ($request->query('token') !== env('DIAG_TOKEN')) {
            abort(403);
        }

        $data = [
            'app' => [
                'env' => env('APP_ENV'),
                'debug' => (bool) env('APP_DEBUG', false),
                'url' => config('app.url'),
            ],
            'health' => 'OK',
            'database' => [
                'default' => config('database.default'),
                'mysql_host' => config('database.connections.mysql.host'),
                'mysql_db' => config('database.connections.mysql.database'),
            ],
        ];

        try {
            DB::connection()->getPdo();
            $data['database']['connected'] = true;
        } catch (\Throwable $e) {
            $data['database']['connected'] = false;
            $data['database']['error'] = $e->getMessage();
        }

        return response()->json($data);
    });
}

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
