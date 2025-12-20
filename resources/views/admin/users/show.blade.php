@extends('layouts.admin')

@section('title', 'User Details - ' . $user->full_name)

@section('admin-content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>User Details</h2>
            <p>View and manage user information</p>
        </div>
        <div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Back to Users
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content">
        <div class="user-detail-content">
            <div class="user-header">
                @php
                    $avatarColors = [
                        'STUDENT' => ['bg' => 'linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%)', 'icon' => 'fa-user-circle'],
                        'INSTRUCTOR' => ['bg' => 'linear-gradient(135deg, #8b5cf6 0%, #ec4899 50%, #f59e0b 100%)', 'icon' => 'fa-chalkboard-teacher'],
                        'ADMIN' => ['bg' => 'linear-gradient(135deg, #ef4444 0%, #f59e0b 50%, #84cc16 100%)', 'icon' => 'fa-user-shield'],
                        'student' => ['bg' => 'linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%)', 'icon' => 'fa-user-circle'],
                        'instructor' => ['bg' => 'linear-gradient(135deg, #8b5cf6 0%, #ec4899 50%, #f59e0b 100%)', 'icon' => 'fa-chalkboard-teacher'],
                        'admin' => ['bg' => 'linear-gradient(135deg, #ef4444 0%, #f59e0b 50%, #84cc16 100%)', 'icon' => 'fa-user-shield'],
                    ];
                    
                    $role = strtoupper($user->role);
                    $avatar = $avatarColors[$role] ?? $avatarColors['STUDENT'];
                @endphp
                <div class="user-avatar-placeholder" style="background: {{ $avatar['bg'] }};">
                    <i class="fas {{ $avatar['icon'] }}"></i>
                </div>
                <div class="user-info">
                    <h3>{{ $user->full_name ?? $user->name }}</h3>
                    <div class="user-meta">
                        <span><i class="fas fa-envelope"></i> Email: <strong>{{ $user->email }}</strong></span>
                        <span><i class="fas fa-user-tag"></i> Role: <strong>{{ ucfirst(strtolower($user->role)) }}</strong></span>
                        <span><i class="fas fa-calendar-alt"></i> Joined: <strong>{{ $user->created_at->format('M d, Y') }}</strong></span>
                    </div>
                </div>
            </div>
            
            <div class="user-stats">
                @php
                    $userRole = strtolower($user->role);
                    
                    // حساب الإحصاءات بناءً على البيانات المتوفرة
                    if($userRole === 'student') {
                        $enrollments = \App\Models\Enrollment::where('student_id', $user->id)->count();
                        $joinRequests = \App\Models\JoinRequest::where('student_id', $user->id)->count();
                    } elseif($userRole === 'instructor') {
                        $courses = \App\Models\Course::where('instructor_id', $user->id)->count();
                        $totalEnrollments = \App\Models\Enrollment::whereHas('course', function($query) use ($user) {
                            $query->where('instructor_id', $user->id);
                        })->count();
                    }
                    
                    // حساب الأيام بشكل صحيح
                    $daysActive = abs(round(\Carbon\Carbon::parse($user->created_at)->diffInDays(now())));
                    $lastActivity = $user->last_login_at ?? $user->created_at;
                    $daysSinceLastActivity = abs(\Carbon\Carbon::parse($lastActivity)->diffInDays(now()));
                    $activityStatus = $daysSinceLastActivity <= 7 ? 'Active' : ($daysSinceLastActivity <= 30 ? 'Inactive' : 'Dormant');
                @endphp
                
                @if($userRole === 'student')
                <div class="stat-box">
                    <i class="fas fa-book-open"></i>
                    <div>
                        <span class="stat-number">{{ $enrollments ?? 0 }}</span>
                        <span class="stat-label">Courses Enrolled</span>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-clock"></i>
                    <div>
                        <span class="stat-number">{{ $joinRequests ?? 0 }}</span>
                        <span class="stat-label">Join Requests</span>
                    </div>
                </div>
                @endif
                
                @if($userRole === 'instructor')
                <div class="stat-box">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <div>
                        <span class="stat-number">{{ $courses ?? 0 }}</span>
                        <span class="stat-label">Courses Created</span>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-users"></i>
                    <div>
                        <span class="stat-number">{{ $totalEnrollments ?? 0 }}</span>
                        <span class="stat-label">Total Students</span>
                    </div>
                </div>
                @endif
                
                <div class="stat-box">
                    <i class="fas fa-calendar"></i>
                    <div>
                        <span class="stat-number">{{ $daysActive }}</span>
                        <span class="stat-label">Days Active</span>
                    </div>
                </div>
                
                <div class="stat-box">
                    <i class="fas fa-chart-line"></i>
                    <div>
                        <span class="stat-number">{{ $activityStatus }}</span>
                        <span class="stat-label">Activity Status</span>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h4>User Information</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <label>User ID:</label>
                        <span>{{ $user->id }}</span>
                    </div>
                    <div class="info-item">
                        <label>Account Status:</label>
                        <span class="badge status-active">Active</span>
                    </div>
                    <div class="info-item">
                        <label>Last Login:</label>
                        <span>{{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Email Verified:</label>
                        @if($user->email_verified_at)
                            <span class="badge status-verified">Verified</span>
                        @else
                            <span class="badge status-suspended">Not Verified</span>
                        @endif
                    </div>
                    <div class="info-item">
                        <label>Username:</label>
                        <span>{{ $user->name }}</span>
                    </div>
                    <div class="info-item">
                        <label>Full Name:</label>
                        <span>{{ $user->full_name }}</span>
                    </div>
                    <div class="info-item">
                        <label>Profile Picture:</label>
                        <span>{{ $user->profile_picture ? 'Uploaded' : 'Not Set' }}</span>
                    </div>
                </div>
            </div>
            
            @php
                // جلب البيانات الحديثة بناءً على الدور
                if($userRole === 'student') {
                    $recentEnrollments = \App\Models\Enrollment::where('student_id', $user->id)
                        ->with('course')
                        ->latest()
                        ->take(3)
                        ->get();
                        
                    $recentJoinRequests = \App\Models\JoinRequest::where('student_id', $user->id)
                        ->with('course')
                        ->latest()
                        ->take(3)
                        ->get();
                        
                } elseif($userRole === 'instructor') {
                    $recentCourses = \App\Models\Course::where('instructor_id', $user->id)
                        ->latest()
                        ->take(3)
                        ->get();
                        
                    $recentJoinRequests = \App\Models\JoinRequest::whereHas('course', function($query) use ($user) {
                            $query->where('instructor_id', $user->id);
                        })
                        ->with(['student', 'course'])
                        ->latest()
                        ->take(3)
                        ->get();
                }
            @endphp
            
            @if($userRole === 'student')
                @if($recentEnrollments->count() > 0)
                <div class="section">
                    <h4>Recent Enrollments</h4>
                    <div class="activity-list">
                        @foreach($recentEnrollments as $enrollment)
                        <div class="activity-item">
                            <i class="fas fa-book activity-icon blue"></i>
                            <div>
                                <p class="activity-text">Enrolled in "{{ $enrollment->course->title ?? 'Course' }}"</p>
                                <p class="activity-subtext">{{ $enrollment->enrolled_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                @if($recentJoinRequests->count() > 0)
                <div class="section">
                    <h4>Recent Join Requests</h4>
                    <div class="activity-list">
                        @foreach($recentJoinRequests as $request)
                        <div class="activity-item">
                            <i class="fas fa-user-plus activity-icon {{ $request->status === 'PENDING' ? 'yellow' : ($request->status === 'ACCEPTED' ? 'green' : 'red') }}"></i>
                            <div>
                                <p class="activity-text">{{ $request->status }} request for "{{ $request->course->title ?? 'Course' }}"</p>
                                <p class="activity-subtext">{{ $request->request_date->format('M d, Y') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endif
            
            @if($userRole === 'instructor')
                @if($recentCourses->count() > 0)
                <div class="section">
                    <h4>Recent Courses</h4>
                    <div class="activity-list">
                        @foreach($recentCourses as $course)
                        <div class="activity-item">
                            <i class="fas fa-chalkboard-teacher activity-icon {{ $course->is_closed ? 'red' : 'green' }}"></i>
                            <div>
                                <p class="activity-text">{{ $course->is_closed ? 'Closed' : 'Created' }} course "{{ $course->title }}"</p>
                                <p class="activity-subtext">{{ $course->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                @if(isset($recentJoinRequests) && $recentJoinRequests->count() > 0)
                <div class="section">
                    <h4>Recent Course Requests</h4>
                    <div class="activity-list">
                        @foreach($recentJoinRequests as $request)
                        <div class="activity-item">
                            <i class="fas fa-user-clock activity-icon {{ $request->status === 'PENDING' ? 'yellow' : ($request->status === 'ACCEPTED' ? 'green' : 'red') }}"></i>
                            <div>
                                <p class="activity-text">{{ $request->student->name ?? 'Student' }} requested to join "{{ $request->course->title ?? 'Course' }}"</p>
                                <p class="activity-subtext">Status: {{ $request->status }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endif
            
            @if($userRole === 'admin')
            <div class="section">
                <h4>Administrator Actions</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Audit Logs:</label>
                        <span>{{ \App\Models\AuditLog::where('actor_id', $user->id)->count() }} actions</span>
                    </div>
                    <div class="info-item">
                        <label>Notifications Sent:</label>
                        <span>{{ \App\Models\Notification::where('recipient_id', $user->id)->count() }} received</span>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="section">
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit User
                    </a>
                    <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline" style="color: #ef4444; border-color: #ef4444;">
                            <i class="fas fa-trash"></i>
                            Delete User
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* CSS Variables */
    :root {
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        
        --blue-50: #eff6ff;
        --blue-100: #dbeafe;
        --blue-500: #3b82f6;
        --blue-600: #2563eb;
        
        --green-500: #10b981;
        --green-600: #059669;
        
        --red-500: #ef4444;
        --red-600: #dc2626;
        
        --yellow-500: #f59e0b;
        --yellow-600: #d97706;
    }
    
    /* Page Header */
    .page-header {
        margin-bottom: 2rem;
    }
    
    .page-header h2 {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }
    
    .page-header p {
        color: var(--gray-600);
        font-size: 1rem;
    }
    
    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        text-decoration: none;
        gap: 0.5rem;
    }
    
    .btn-outline {
        background: white;
        border-color: var(--gray-300);
        color: var(--gray-700);
    }
    
    .btn-outline:hover {
        background: var(--gray-50);
        border-color: var(--gray-400);
    }
    
    .btn-primary {
        background: var(--blue-500);
        color: white;
        border-color: var(--blue-500);
    }
    
    .btn-primary:hover {
        background: var(--blue-600);
        border-color: var(--blue-600);
    }
    
    /* Card */
    .card {
        background: white;
        border-radius: 0.75rem;
        border: 1px solid var(--gray-200);
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }
    
    .card-content {
        padding: 1.5rem;
    }
    
    /* User Detail Content */
    .user-detail-content {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }
    
    /* User Header */
    .user-header {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
    }
    
    .user-avatar-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
        flex-shrink: 0;
    }
    
    .user-info {
        flex: 1;
    }
    
    .user-info h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 1rem;
    }
    
    .user-meta {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .user-meta span {
        font-size: 0.875rem;
        color: var(--gray-600);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .user-meta strong {
        color: var(--gray-900);
        font-weight: 500;
    }
    
    .user-meta i {
        color: var(--blue-500);
        width: 1rem;
    }
    
    /* User Stats */
    .user-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .stat-box {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        background: var(--gray-50);
        border-radius: 0.75rem;
        border: 1px solid var(--gray-200);
        transition: all 0.2s;
    }
    
    .stat-box:hover {
        background: var(--gray-100);
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .stat-box i {
        font-size: 1.75rem;
        color: var(--blue-500);
        flex-shrink: 0;
    }
    
    .stat-number {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1.2;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: var(--gray-600);
        line-height: 1.2;
    }
    
    /* Sections */
    .section {
        border-top: 1px solid var(--gray-200);
        padding-top: 1.5rem;
    }
    
    .section h4 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 1rem;
    }
    
    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .info-item label {
        font-size: 0.875rem;
        color: var(--gray-600);
        font-weight: 500;
    }
    
    .info-item span {
        font-size: 0.875rem;
        color: var(--gray-800);
    }
    
    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        width: fit-content;
    }
    
    .status-active {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    
    .status-verified {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    
    .status-suspended {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    
    /* Activity List */
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .activity-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: 0.75rem;
        border: 1px solid var(--gray-200);
        transition: all 0.2s;
    }
    
    .activity-item:hover {
        background: var(--gray-100);
    }
    
    .activity-icon {
        font-size: 1rem;
        margin-top: 0.125rem;
        flex-shrink: 0;
    }
    
    .activity-icon.blue { color: var(--blue-500); }
    .activity-icon.green { color: var(--green-500); }
    .activity-icon.red { color: var(--red-500); }
    .activity-icon.yellow { color: var(--yellow-500); }
    
    .activity-text {
        font-size: 0.875rem;
        color: var(--gray-900);
        margin: 0;
        font-weight: 500;
    }
    
    .activity-subtext {
        font-size: 0.75rem;
        color: var(--gray-600);
        margin: 0.25rem 0 0 0;
    }
    
    /* Forms */
    form {
        margin: 0;
        padding: 0;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .user-header {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
        
        .user-meta {
            align-items: center;
        }
        
        .user-stats {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .section > div:last-child {
            flex-direction: column;
        }
        
        .section > div:last-child .btn {
            width: 100%;
        }
    }
    
    @media (max-width: 480px) {
        .user-stats {
            grid-template-columns: 1fr;
        }
        
        .page-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .page-header > div:first-child {
            width: 100%;
        }
    }
</style>
@endpush