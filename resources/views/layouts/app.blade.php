<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes, viewport-fit=cover">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA Meta Tags for App-like Experience -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#FF6101">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ time() }}">

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md shadow-sm premium-navbar sticky-top">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}" style="font-weight: 600;">
                    <img src="{{ asset('images/varondi.png') }}" alt="Varondi" class="navbar-logo" style="height: 40px; margin-right: 0.5rem;">
                    <span class="d-none d-md-inline navbar-brand-text">{{ config('app.name', 'Task Manager') }}</span>
                </a>

                @auth
                <!-- User Controls - Always Visible -->
                <div class="d-flex align-items-center gap-2 order-md-3">
                    <!-- Dark Mode Toggle -->
                    <button class="btn btn-outline-secondary dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode" style="border-radius: 50px; padding: 0.35rem 0.75rem;">
                        <span class="toggle-icon">
                            <i class="bi bi-sun-fill"></i>
                        </span>
                    </button>

                    <!-- Logout Button -->
                    <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                        @csrf
                        <button type="submit" class="btn btn-primary logout-btn" title="Logout" style="border-radius: 6px; padding: 0.4rem 1rem; font-weight: 500;">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="d-none d-md-inline ms-1">Logout</span>
                        </button>
                    </form>
                </div>
                @endauth

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

                    <!-- Right Side Of Navbar (Guest Only) -->
                    <ul class="navbar-nav ms-auto">
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
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
                        // In dark mode, show sun icon (to switch to light)
                        toggleIcon.classList.remove('bi-moon-fill');
                        toggleIcon.classList.add('bi-sun-fill');
                    } else {
                        // In light mode, show moon icon (to switch to dark)
                        toggleIcon.classList.remove('bi-sun-fill');
                        toggleIcon.classList.add('bi-moon-fill');
                    }
                }
            }

            // Check for saved theme preference or default to dark mode
            let savedTheme = localStorage.getItem('theme');

            @auth
            if (!savedTheme) {
                savedTheme = '{{ auth()->user()->dark_mode ?? "dark" }}';
            }
            @else
            if (!savedTheme) {
                savedTheme = 'dark';
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
