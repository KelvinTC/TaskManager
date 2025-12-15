<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

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
                'key_set' => (bool) config('app.key'),
            ],
            'health' => 'OK',
            'database' => [
                'default' => config('database.default'),
                'mysql_host' => config('database.connections.mysql.host'),
                'mysql_db' => config('database.connections.mysql.database'),
            ],
            'php' => [
                'version' => PHP_VERSION,
                'pdo_mysql_loaded' => extension_loaded('pdo_mysql'),
            ],
            'session' => [
                'driver' => config('session.driver'),
                'cookie' => config('session.cookie'),
                'path' => config('session.files'),
            ],
            'storage' => [
                'logs_writable' => is_writable(storage_path('logs')),
                'framework_writable' => is_writable(storage_path('framework')),
            ],
            'queue' => [
                'default' => config('queue.default'),
            ],
            'cache' => [
                'driver' => config('cache.default'),
            ],
        ];

        try {
            DB::connection()->getPdo();
            $data['database']['connected'] = true;
            // Quick table presence check without throwing
            try {
                $data['database']['has_users_table'] = \Illuminate\Support\Facades\Schema::hasTable('users');
                $data['database']['migrations_table'] = \Illuminate\Support\Facades\Schema::hasTable('migrations');
            } catch (\Throwable $e) {
                $data['database']['has_users_table'] = null;
                $data['database']['migrations_table'] = null;
            }
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

// Ensure the 'web' middleware is applied so sessions/auth state are available
Route::middleware(['web','auth'])->group(function () {
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

// Queue diagnostics (behind DIAG_TOKEN)
if (!empty(env('DIAG_TOKEN'))) {
    Route::get('/queue/health', function (Request $request) {
        if ($request->query('token') !== env('DIAG_TOKEN')) {
            abort(403);
        }

        $connection = config('queue.default');
        $summary = [
            'connection' => $connection,
            'jobs_table_exists' => \Illuminate\Support\Facades\Schema::hasTable('jobs'),
            'failed_jobs_table_exists' => \Illuminate\Support\Facades\Schema::hasTable('failed_jobs'),
        ];

        try {
            $summary['pending_jobs'] = \Illuminate\Support\Facades\DB::table('jobs')->count();
        } catch (\Throwable $e) {
            $summary['pending_jobs'] = null;
        }
        try {
            $summary['failed_jobs'] = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
        } catch (\Throwable $e) {
            $summary['failed_jobs'] = null;
        }

        $summary['last_ping'] = \Illuminate\Support\Facades\Cache::get('queue:last_ping');

        // Optionally dispatch a quick ping job to update last_ping
        try {
            dispatch(new \App\Jobs\QueuePing());
            $summary['ping_dispatched'] = true;
        } catch (\Throwable $e) {
            $summary['ping_dispatched'] = false;
            $summary['dispatch_error'] = $e->getMessage();
        }

        return response()->json($summary);
    });
}
