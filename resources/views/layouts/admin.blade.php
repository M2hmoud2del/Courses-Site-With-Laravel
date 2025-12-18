<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Admin Dashboard') - LearnHub</title>
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @stack('styles')
</head>
<body class="admin-body">
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-container">
            <div class="nav-left">
                <div class="logo" onclick="window.location='{{ route('admin.dashboard') }}'">
                    <i class="fas fa-book-open"></i>
                    <span>LearnHub Admin</span>
                </div>
                <div class="nav-links">
                    <a href="{{ route('admin.dashboard') }}" class="nav-btn {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="nav-btn {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                    <a href="{{ route('admin.courses.index') }}" class="nav-btn {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                        <i class="fas fa-book-open"></i>
                        <span>Courses</span>
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="nav-btn {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="fas fa-folder-tree"></i>
                        <span>Categories</span>
                    </a>
                    <a href="{{ route('admin.statistics') }}" class="nav-btn {{ request()->routeIs('admin.statistics') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>Statistics</span>
                    </a>
                </div>
            </div>
            <div class="nav-right">
                <span class="admin-name">
                    <i class="fas fa-user-shield"></i>
                    {{ auth()->user()?->name ?? 'Admin' }}
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-ghost">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>

        </div>
    </nav>

   <!-- Main Container -->
    <div class="main-container">
        <div class="dashboard-grid admin-grid">
            <!-- Sidebar - ضع هنا السايدبار الثابت -->
            <aside class="sidebar">
                <div class="card">
                    <div class="card-header">
                        <h3>Quick Access</h3>
                    </div>
                    <div class="card-content sidebar-menu">
                        <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            <span>User Management</span>
                        </a>
                        <a href="{{ route('admin.courses.index') }}" class="menu-item {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                            <i class="fas fa-book-open"></i>
                            <span>Course Management</span>
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <i class="fas fa-folder-tree"></i>
                            <span>Categories</span>
                        </a>
                        <a href="{{ route('admin.statistics') }}" class="menu-item {{ request()->routeIs('admin.statistics') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar"></i>
                            <span>Analytics</span>
                        </a>
                    </div>
                </div>

                <!-- System Activity -->
                <div class="card">
                    <div class="card-header">
                        <h3>System Activity</h3>
                    </div>
                    <div class="card-content activity-list">
                        <div class="activity-item">
                            <i class="fas fa-check-circle activity-icon blue"></i>
                            <div>
                                <p class="activity-text"><span id="pending-requests-count">{{ $layoutPendingRequests }}</span> pending join requests</p>
                                <p class="activity-subtext">
                                    {{ $layoutPendingRequests > 0 ? 'Requires attention' : 'All clear' }}
                                </p>
                            </div>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-check-circle activity-icon green"></i>
                            <div>
                                <p class="activity-text"><span id="active-courses-count">{{ $layoutActiveCourses }}</span> active courses</p>
                                <p class="activity-subtext">System running smoothly</p>
                            </div>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-clock activity-icon yellow"></i>
                            <div>
                                <p class="activity-text">Daily backup scheduled</p>
                                <p class="activity-subtext">2:00 AM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="main-content">
                @yield('admin-content')
            </main>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <!-- Scripts -->
    <script src="{{ asset('js/admin.js') }}"></script>
    @stack('scripts')
    
    <script>
        // Toast functionality
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast show ${type}`;
            
            setTimeout(() => {
                toast.className = 'toast';
            }, 3000);
        }
    </script>
</body>
</html>