<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Instructor Dashboard') - LearnHub</title>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/instructor.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles')
</head>

<body class="admin-body">
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-container">
            <div class="nav-left">
                <div class="logo" onclick="window.location='{{ route('instructor.dashboard') }}'">
                    <i class="fas fa-book-open"></i>
                    <span>LearnHub Instructor</span>
                </div>
                <div class="nav-links">
                    <a href="{{ route('instructor.dashboard') }}"
                        class="nav-btn {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('instructor.courses.index') }}"
                        class="nav-btn {{ request()->routeIs('instructor.courses.*') || request()->routeIs('instructor.content.*') ? 'active' : '' }}">
                        <i class="fas fa-book-open"></i>
                        <span>Courses</span>
                    </a>
                    <a href="{{ route('instructor.join-requests.index') }}"
                        class="nav-btn {{ request()->routeIs('instructor.join-requests.*') ? 'active' : '' }}">
                        <i class="fas fa-user-clock"></i>
                        <span>Join Requests</span>
                    </a>
                    <a href="{{ route('instructor.enrollments.index') }}"
                        class="nav-btn {{ request()->routeIs('instructor.enrollments.*') ? 'active' : '' }}">
                        <i class="fas fa-user-graduate"></i>
                        <span>Enrollments</span>
                    </a>
                    <a href="{{ route('instructor.analytics') }}"
                        class="nav-btn {{ request()->routeIs('instructor.analytics') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                </div>
            </div>
            <div class="nav-right">
                <span class="admin-name">
                    <i class="fas fa-user-tie"></i>
                    {{ auth()->user()?->name ?? 'Instructor' }}
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
            <aside class="sidebar">
                <div class="card">
                    <div class="card-header">
                        <h3>Quick Access</h3>
                    </div>
                    <div class="card-content sidebar-menu">
                        <a href="{{ route('instructor.dashboard') }}"
                            class="menu-item {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('instructor.courses.index') }}"
                            class="menu-item {{ request()->routeIs('instructor.courses.*') ? 'active' : '' }}">
                            <i class="fas fa-book-open"></i>
                            <span>Course Management</span>
                        </a>
                        <a href="{{ route('instructor.join-requests.index') }}"
                            class="menu-item {{ request()->routeIs('instructor.join-requests.*') ? 'active' : '' }}">
                            <i class="fas fa-user-clock"></i>
                            <span>Join Requests</span>
                            @php
                                $pendingCount = \App\Models\JoinRequest::whereHas('course', function ($q) {
                                    $q->where('instructor_id', auth()->id());
                                })
                                    ->where('status', 'PENDING')
                                    ->count();
                            @endphp
                            @if ($pendingCount > 0)
                                <span class="badge"
                                    style="background: var(--red-500); color: white; margin-left: auto; font-size: 0.75rem; padding: 2px 6px; border-radius: 10px;">{{ $pendingCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('instructor.enrollments.index') }}"
                            class="menu-item {{ request()->routeIs('instructor.enrollments.*') ? 'active' : '' }}">
                            <i class="fas fa-user-graduate"></i>
                            <span>Enrollments</span>
                        </a>
                        <a href="{{ route('instructor.analytics') }}"
                            class="menu-item {{ request()->routeIs('instructor.analytics') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
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
                                <p class="activity-text"><span
                                        id="pending-requests-count">{{ $pendingCount ?? 0 }}</span> pending join
                                    requests</p>
                                <p class="activity-subtext">
                                    {{ ($pendingCount ?? 0) > 0 ? 'Requires attention' : 'All clear' }}
                                </p>
                            </div>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-check-circle activity-icon green"></i>
                            <div>
                                <p class="activity-text"><span id="active-courses-count">
                                        @php
                                            $activeCourses = \App\Models\Course::where('instructor_id', auth()->id())
                                                ->where('is_closed', false)
                                                ->count();
                                        @endphp
                                        {{ $activeCourses }}
                                    </span> active courses</p>
                                <p class="activity-subtext">Open for enrollment</p>
                            </div>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-clock activity-icon yellow"></i>
                            <div>
                                <p class="activity-text">Total students across courses</p>
                                <p class="activity-subtext">
                                    @php
                                        $totalStudents = \App\Models\Enrollment::whereHas('course', function ($q) {
                                            $q->where('instructor_id', auth()->id());
                                        })
                                            ->distinct('student_id')
                                            ->count('student_id');
                                    @endphp
                                    {{ $totalStudents }} students
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="main-content">
                @yield('instructor-content')
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

        // Show any flash messages
        @if (session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif

        @if (session('error'))
            showToast('{{ session('error') }}', 'error');
        @endif
    </script>
</body>

</html>
