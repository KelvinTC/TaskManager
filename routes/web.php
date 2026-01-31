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

    // Update user WhatsApp settings
    Route::get('/diag/update-user-whatsapp', function (Request $request) {
        if ($request->query('token') !== env('DIAG_TOKEN')) {
            abort(403);
        }

        $email = $request->query('email');
        $phone = $request->query('phone'); // e.g., +263783017279

        if (!$email || !$phone) {
            return response()->json([
                'error' => 'Please provide ?email=user@example.com&phone=+263XXXXXXXXX parameters',
            ]);
        }

        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found with email: ' . $email]);
        }

        $user->phone = $phone;
        $user->preferred_channel = 'whatsapp';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'preferred_channel' => $user->preferred_channel,
            ],
        ]);
    });

    // Check task and user settings
    Route::get('/diag/check-task', function (Request $request) {
        if ($request->query('token') !== env('DIAG_TOKEN')) {
            abort(403);
        }

        $taskId = $request->query('task_id');

        if (!$taskId) {
            // Get latest task
            $task = \App\Models\Task::with(['creator', 'assignedTo'])->latest()->first();
        } else {
            $task = \App\Models\Task::with(['creator', 'assignedTo'])->find($taskId);
        }

        if (!$task) {
            return response()->json(['error' => 'No task found']);
        }

        return response()->json([
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
            ],
            'creator' => [
                'name' => $task->creator->name,
                'email' => $task->creator->email,
                'phone' => $task->creator->phone ?? 'NOT SET',
                'preferred_channel' => $task->creator->preferred_channel ?? 'NOT SET',
            ],
            'assigned_to' => $task->assignedTo ? [
                'name' => $task->assignedTo->name,
                'email' => $task->assignedTo->email,
                'phone' => $task->assignedTo->phone ?? 'NOT SET',
                'preferred_channel' => $task->assignedTo->preferred_channel ?? 'NOT SET',
            ] : null,
        ]);
    });

    // Test send WhatsApp notification endpoint
    Route::get('/diag/test-send', function (Request $request) {
        if ($request->query('token') !== env('DIAG_TOKEN')) {
            abort(403);
        }

        try {
            // Find a user with WhatsApp enabled
            $user = \App\Models\User::where('preferred_channel', 'whatsapp')
                ->whereNotNull('phone')
                ->first();

            if (!$user) {
                return response()->json([
                    'error' => 'No user found with WhatsApp enabled and phone number set',
                ]);
            }

            // Find or create a test task
            $task = \App\Models\Task::first();
            if (!$task) {
                return response()->json(['error' => 'No tasks found in database']);
            }

            // Send notification
            $user->notify(new \App\Notifications\TaskAssigned($task));

            return response()->json([
                'success' => true,
                'message' => 'Notification sent',
                'user' => [
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'preferred_channel' => $user->preferred_channel,
                ],
                'task' => $task->title,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    });

    // WhatsApp configuration check endpoint
    Route::get('/diag/whatsapp', function (Request $request) {
        if ($request->query('token') !== env('DIAG_TOKEN')) {
            abort(403);
        }

        $provider = config('services.whatsapp.provider');

        $config = [
            'provider' => $provider,
            'env_vars' => [
                'WHATSAPP_PROVIDER' => env('WHATSAPP_PROVIDER'),
                'WHATSAPP_USE_TEMPLATES' => env('WHATSAPP_USE_TEMPLATES'),
            ],
        ];

        // Check provider-specific config
        switch ($provider) {
            case 'ultramsg':
                $config['ultramsg'] = [
                    'instance_id' => env('ULTRAMSG_INSTANCE_ID') ? '✓ Set' : '✗ Not set',
                    'token' => env('ULTRAMSG_TOKEN') ? '✓ Set' : '✗ Not set',
                ];
                break;
            case 'meta':
                $config['meta'] = [
                    'token' => env('META_WHATSAPP_TOKEN') ? '✓ Set' : '✗ Not set',
                    'phone_id' => env('META_WHATSAPP_PHONE_ID') ? '✓ Set' : '✗ Not set',
                ];
                break;
            case 'twilio':
                $config['twilio'] = [
                    'sid' => env('TWILIO_SID') ? '✓ Set' : '✗ Not set',
                    'token' => env('TWILIO_TOKEN') ? '✓ Set' : '✗ Not set',
                    'from' => env('TWILIO_WHATSAPP_FROM') ? '✓ Set' : '✗ Not set',
                ];
                break;
        }

        // Check queue status
        try {
            $config['queue'] = [
                'connection' => config('queue.default'),
                'pending_jobs' => \DB::table('jobs')->count(),
                'failed_jobs' => \DB::table('failed_jobs')->count(),
            ];
        } catch (\Throwable $e) {
            $config['queue'] = ['error' => $e->getMessage()];
        }

        return response()->json($config);
    });

    // Migration runner endpoint
    Route::get('/diag/migrate', function (Request $request) {
        if ($request->query('token') !== env('DIAG_TOKEN')) {
            abort(403);
        }

        $data = [
            'migrations_run' => false,
            'seeder_run' => false,
            'errors' => [],
        ];

        try {
            // Run migrations
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $data['migrations_run'] = true;
            $data['migration_output'] = \Illuminate\Support\Facades\Artisan::output();
        } catch (\Throwable $e) {
            $data['errors'][] = 'Migration failed: ' . $e->getMessage();
        }

        try {
            // Run SuperAdminSeeder
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => 'SuperAdminSeeder',
                '--force' => true,
            ]);
            $data['seeder_run'] = true;
            $data['seeder_output'] = \Illuminate\Support\Facades\Artisan::output();
        } catch (\Throwable $e) {
            $data['errors'][] = 'Seeder failed: ' . $e->getMessage();
        }

        return response()->json($data);
    });

    // Clear all caches endpoint
    Route::get('/diag/clear-cache', function (Request $request) {
        if ($request->query('token') !== env('DIAG_TOKEN')) {
            abort(403);
        }

        $data = ['cleared' => []];

        \Illuminate\Support\Facades\Artisan::call('config:clear');
        $data['cleared'][] = 'config';

        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        $data['cleared'][] = 'cache';

        \Illuminate\Support\Facades\Artisan::call('route:clear');
        $data['cleared'][] = 'route';

        \Illuminate\Support\Facades\Artisan::call('view:clear');
        $data['cleared'][] = 'view';

        return response()->json($data);
    });

    // User diagnostic and fix endpoint
    Route::get('/diag/users', function (Request $request) {
        if ($request->query('token') !== env('DIAG_TOKEN')) {
            abort(403);
        }

        $expectedEmail = env('SUPERADMIN_EMAIL', 'superadmin@taskmanager.com');
        $expectedPassword = env('SUPERADMIN_PASSWORD', 'password123');
        $fix = $request->query('fix') === 'true';
        $create = $request->query('create') === 'true';

        $data = [
            'expected_credentials' => [
                'email' => $expectedEmail,
                'password' => $expectedPassword,
            ],
            'all_users' => [],
            'superadmin_check' => null,
            'password_check' => null,
            'fixed' => false,
            'created' => false,
        ];

        try {
            // Get all users
            $users = \App\Models\User::all();
            foreach ($users as $user) {
                $data['all_users'][] = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'password_hash_start' => substr($user->password, 0, 10),
                    'is_bcrypt' => str_starts_with($user->password, '$2y$'),
                ];
            }

            // Check superadmin user
            $superadmin = \App\Models\User::where('email', $expectedEmail)->first();

            // Create user if requested and doesn't exist
            if ($create && !$superadmin) {
                $superadmin = \App\Models\User::create([
                    'name' => 'Super Admin',
                    'email' => $expectedEmail,
                    'password' => $expectedPassword,
                    'role' => 'super_admin',
                    'phone' => null,
                    'preferred_channel' => 'in_app',
                    'email_verified_at' => now(),
                ]);
                $data['created'] = true;
                $data['message'] = 'Superadmin user created successfully!';
            }

            if ($superadmin) {
                $data['superadmin_check'] = [
                    'found' => true,
                    'id' => $superadmin->id,
                    'email' => $superadmin->email,
                    'role' => $superadmin->role,
                    'is_bcrypt_hashed' => str_starts_with($superadmin->password, '$2y$'),
                ];

                // Test password
                $passwordMatches = \Illuminate\Support\Facades\Hash::check($expectedPassword, $superadmin->password);
                $data['password_check'] = [
                    'matches' => $passwordMatches,
                    'hash_start' => substr($superadmin->password, 0, 30),
                ];

                // Fix if requested
                if ($fix && !$passwordMatches) {
                    $superadmin->password = \Illuminate\Support\Facades\Hash::make($expectedPassword);
                    $superadmin->save();
                    $data['fixed'] = true;
                    $data['message'] = 'Password has been reset to: ' . $expectedPassword;
                }
            } else {
                $data['superadmin_check'] = [
                    'found' => false,
                    'message' => 'User not found. Add &create=true to create the user.',
                ];
            }
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            $data['trace'] = $e->getTraceAsString();
        }

        return response()->json($data);
    });
}

Route::get('/', function () {
    return redirect()->route('login');
});

// Temporary asset diagnostic route
Route::get('/test-assets', function () {
    return view('test-assets');
});

// CSRF Test Route
Route::get('/test-csrf', function () {
    return response()->json([
        'session_id' => session()->getId(),
        'csrf_token' => csrf_token(),
        'session_token' => session()->token(),
        'cookies' => request()->cookies->all(),
    ]);
});

Route::post('/test-csrf', function () {
    return response()->json(['success' => true, 'message' => 'CSRF validation passed!']);
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
        Route::delete('/users/{user}', [UserManagementController::class, 'deleteUser'])->name('users.delete');
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
