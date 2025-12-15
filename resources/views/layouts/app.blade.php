<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes, viewport-fit=cover">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA Meta Tags for App-like Experience -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#667eea">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Styles & Scripts built by Vite -->
    @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])

    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <i class="bi bi-kanban"></i> {{ config('app.name', 'Task Manager') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">
                                    <i class="bi bi-list-task"></i> Tasks
                                </a>
                            </li>
                            @if(Auth::user()->canInviteUsers())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                        <i class="bi bi-people"></i> User Management
                                    </a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto align-items-center">
                        <!-- Dark Mode Toggle -->
                        <li class="nav-item me-3">
                            <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode">
                                <span class="toggle-icon">
                                    <i class="bi bi-sun-fill"></i>
                                </span>
                            </button>
                        </li>

                        <!-- Authentication Links -->
                        @guest
                            <!-- Always show Login link for guests; avoid hiding it behind Route::has in production -->
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <!-- Visible username chip -->
                            <li class="nav-item d-none d-md-flex align-items-center me-2">
                                <span class="navbar-text">
                                    <i class="bi bi-person-circle"></i>
                                    {{ Auth::user()->name }}
                                    <span class="badge bg-primary ms-1">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</span>
                                </span>
                            </li>

                            <!-- Non-JS Logout fallback (plain form button) -->
                            <li class="nav-item d-none d-md-flex">
                                <form id="logout-form-inline" action="{{ route('logout') }}" method="POST" class="mb-0">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-light btn-sm">
                                        <i class="bi bi-box-arrow-right"></i> {{ __('Logout') }}
                                    </button>
                                </form>
                            </li>

                            <!-- Dropdown with extra links for all screen sizes -->
                            <li class="nav-item dropdown d-md-none">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                                    <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('dashboard') }}">
                                        <i class="bi bi-speedometer2"></i> Dashboard
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right"></i> {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>

        <!-- Mobile Bottom Navigation -->
        @auth
        <nav class="mobile-bottom-nav d-md-none">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('tasks.index') }}" class="nav-item {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                <i class="bi bi-list-task"></i>
                <span>Tasks</span>
            </a>
            @if(Auth::user()->canCreateTasks())
            <a href="{{ route('tasks.create') }}" class="nav-item {{ request()->routeIs('tasks.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Create</span>
            </a>
            @endif
            @if(Auth::user()->canInviteUsers())
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Users</span>
            </a>
            @endif
        </nav>
        @endauth
    </div>

    <!-- Dark Mode Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const html = document.documentElement;
            const toggleIcon = darkModeToggle?.querySelector('.toggle-icon i');

            // Function to set theme
            function setTheme(theme) {
                html.setAttribute('data-theme', theme);

                if (toggleIcon) {
                    if (theme === 'dark') {
                        toggleIcon.classList.remove('bi-sun-fill');
                        toggleIcon.classList.add('bi-moon-fill');
                    } else {
                        toggleIcon.classList.remove('bi-moon-fill');
                        toggleIcon.classList.add('bi-sun-fill');
                    }
                }
            }

            // Check for saved theme preference or default to light mode
            let savedTheme = localStorage.getItem('theme');

            @auth
            if (!savedTheme) {
                savedTheme = '{{ auth()->user()->dark_mode ?? "light" }}';
            }
            @else
            if (!savedTheme) {
                savedTheme = 'light';
            }
            @endauth

            // Set initial theme
            setTheme(savedTheme);

            // Toggle theme on button click
            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const currentTheme = html.getAttribute('data-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                    setTheme(newTheme);
                    localStorage.setItem('theme', newTheme);

                    // Save to database if user is authenticated
                    @auth
                    fetch('/user/update-theme', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ dark_mode: newTheme })
                    }).catch(error => console.error('Error saving theme:', error));
                    @endauth
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
