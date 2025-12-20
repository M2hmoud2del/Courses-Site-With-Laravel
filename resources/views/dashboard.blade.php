@extends('layouts.student')

@section('content')
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-container">
            <div class="nav-left">
                <div class="logo" onclick="changeView('dashboard')">
                    <i class="fas fa-book-open"></i>
                    <span>LearnHub</span>
                </div>
                <div class="nav-links">
                    <button class="nav-btn active" data-view="dashboard" onclick="changeView('dashboard')">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </button>
                    <button class="nav-btn" data-view="courses" onclick="changeView('courses')">
                        <i class="fas fa-book-open"></i>
                        <span>Browse Courses</span>
                    </button>
                    <button class="nav-btn" data-view="enrolled" onclick="changeView('enrolled')">
                        <i class="fas fa-check-circle"></i>
                        <span>My Courses</span>
                    </button>
                    <button class="nav-btn" data-view="profile" onclick="changeView('profile')">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </button>
                </div>
            </div>
            <div class="nav-right">
                <button class="icon-btn" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <span id="notif-badge" class="badge-dot" style="display: none;"></span>
                </button>
                <!-- Logout Form -->
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-ghost" style="border: none; cursor: pointer;">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>


    <!-- Notifications Panel -->
    <div id="notifications-panel" class="notifications-panel" style="display: none;">
        <div class="panel-header">
            <h3>Notifications</h3>
            <button class="icon-btn" onclick="toggleNotifications()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="panel-content">
            <div id="notifications-list"></div>
        </div>
        <div class="panel-footer" id="notifications-footer" style="display: none;">
             <button class="btn btn-ghost btn-sm btn-full" onclick="markAllRead()" id="mark-all-read-btn">Mark all as read</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <div class="dashboard-grid">
            <!-- Sidebar -->
            <aside class="sidebar">
                <!-- Profile Card -->
                <div class="card profile-card">
                    <div class="card-header text-center">
                        <div class="avatar avatar-lg">
                            <span id="profile-avatar"></span>
                        </div>
                        <h3 id="profile-name"></h3>
                        <p id="profile-email"></p>
                    </div>
                    <div class="card-content">
                        <button class="btn btn-outline btn-full" onclick="changeView('profile')">
                            <i class="fas fa-edit"></i>
                            Edit Profile
                        </button>
                    </div>
                </div>

                <!-- My Courses Widget -->
                <div class="card">
                    <div class="card-header">
                        <h3>My Courses</h3>
                    </div>
                    <div class="card-content">
                        <div id="sidebar-courses"></div>
                    </div>
                </div>

                <!-- Notifications Widget -->
                <div class="card">
                    <div class="card-header">
                        <h3>Notifications</h3>
                        <span id="notif-count-badge" class="badge badge-secondary" style="display: none;"></span>
                    </div>
                    <div class="card-content">
                        <div id="sidebar-notifications"></div>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="main-content">
                <!-- Dashboard View -->
                <div id="view-dashboard" class="view-content" style="display: block;">
                    <div class="welcome-banner">
                        <h1 id="welcome-text">Welcome back! 👋</h1>
                        <p>Continue your learning journey and achieve your goals.</p>
                    </div>

                    <!-- Join Requests -->
                    <div id="join-requests-section" class="card" style="display: none;">
                        <div class="card-header">
                            <h3>Join Requests</h3>
                            <p>Track your course enrollment requests</p>
                        </div>
                        <div class="card-content">
                            <div id="join-requests-list"></div>
                        </div>
                    </div>

                    <!-- Course Recommendations -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Recommended Courses</h3>
                            <p>Discover courses you might like</p>
                        </div>
                        <div class="card-content">
                            <div id="recommended-courses" class="course-grid"></div>
                        </div>
                    </div>
                </div>

                <!-- Browse Courses View -->
                <div id="view-courses" class="view-content" style="display: none;">
                    <div class="page-header">
                        <h1>Browse Courses</h1>
                        <p>Explore our course catalog and find your next learning adventure</p>
                    </div>

                    <!-- Filters -->
                    <div class="card">
                        <div class="card-content">
                            <div class="filters-grid">
                                <div class="input-with-icon">
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="search-input" class="form-input" placeholder="Search courses..." oninput="filterCourses()">
                                </div>
                                <select id="category-filter" class="form-select" onchange="filterCourses()">
                                    <option value="all">All Categories</option>
                                </select>
                                <select id="price-filter" class="form-select" onchange="filterCourses()">
                                    <option value="all">All Prices</option>
                                    <option value="free">Free</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Course Grid -->
                    <div id="courses-grid" class="course-grid"></div>
                    <div id="no-courses-message" class="empty-state" style="display: none;">
                        <p>No courses found matching your criteria</p>
                    </div>
                </div>

                <!-- Enrolled Courses View -->
                <div id="view-enrolled" class="view-content" style="display: none;">
                    <div class="page-header">
                        <h1>My Courses</h1>
                        <p>Continue your learning journey</p>
                    </div>
                    <div id="enrolled-courses" class="course-grid"></div>
                    <div id="no-enrolled-message" class="empty-state" style="display: none;">
                        <i class="fas fa-book-open fa-3x"></i>
                        <p>You haven't enrolled in any courses yet</p>
                        <button class="btn btn-primary" onclick="changeView('courses')">Browse Courses</button>
                    </div>
                </div>

                <!-- Profile View -->
                <div id="view-profile" class="view-content" style="display: none;">
                    <div class="profile-container">
                        <!-- Profile Settings -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Profile Information</h3>
                                <p>Update your account's profile information.</p>
                            </div>
                            <div class="card-content">
                                <form id="profile-form" onsubmit="event.preventDefault(); saveProfile();">
                                    <div class="profile-avatar-section text-center" style="margin-bottom: 2rem;">
                                        <div class="avatar avatar-xl" style="position: relative; overflow: visible; display: inline-flex;">
                                            <span id="profile-avatar-large"></span>
                                            <img id="profile-avatar-img" src="" alt="Profile" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                            <button type="button" class="btn-icon-small" onclick="document.getElementById('avatar-upload').click()" 
                                                style="position: absolute; bottom: 0; right: -0.5rem; background: var(--blue-600); color: white; border-radius: 50%; width: 2rem; height: 2rem; border: 2px solid white; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                <i class="fas fa-camera" style="font-size: 0.875rem;"></i>
                                            </button>
                                        </div>
                                        <input type="file" id="avatar-upload" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                                        <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.5rem;">Allowed *.jpeg, *.jpg, *.png, *.gif</p>
                                    </div>

                                    <div class="form-group">
                                        <label for="profile-name-input">Name</label>
                                        <input type="text" id="profile-name-input" class="form-input" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="profile-email-input">Email</label>
                                        <input type="email" id="profile-email-input" class="form-input" disabled style="background-color: var(--gray-50); cursor: not-allowed;">
                                        <small style="color: var(--gray-500);">Email address cannot be changed.</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Role</label>
                                        <input type="text" id="profile-role-input" class="form-input capitalize" disabled style="background-color: var(--gray-50); cursor: not-allowed;">
                                    </div>

                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </form>
                            </div>
                        </div>

                        <!-- Update Password -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Update Password</h3>
                                <p>Ensure your account is using a long, random password to stay secure.</p>
                            </div>
                            <div class="card-content">
                                <form id="password-form" onsubmit="event.preventDefault(); changePassword();">
                                    <div class="form-group">
                                        <label for="current_password">Current Password</label>
                                        <input type="password" id="current_password" class="form-input" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="new_password">New Password</label>
                                        <input type="password" id="new_password" class="form-input" required minlength="8">
                                    </div>

                                    <div class="form-group">
                                        <label for="password_confirmation">Confirm Password</label>
                                        <input type="password" id="password_confirmation" class="form-input" required minlength="8">
                                    </div>

                                    <button type="submit" class="btn btn-primary">Save Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Course Details Modal -->
    <div id="course-modal" class="modal" style="display: none;">
        <div class="modal-overlay" onclick="closeCourseModal()"></div>
        <div class="modal-content modal-lg">
            <button class="modal-close" onclick="closeCourseModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-header">
                <h2 id="modal-course-title"></h2>
                <p id="modal-course-instructor"></p>
            </div>
            <div class="modal-body">
                <div class="course-image-modal"></div>
                <div class="course-badges" id="modal-course-badges"></div>
                <div class="course-description">
                    <h4>Description</h4>
                    <p id="modal-course-description"></p>
                </div>
                <div class="course-price-section">
                    <span id="modal-course-price" class="price-large"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button id="modal-action-btn" class="btn btn-primary btn-full"></button>
            </div>
        </div>
    </div>
@endsection
