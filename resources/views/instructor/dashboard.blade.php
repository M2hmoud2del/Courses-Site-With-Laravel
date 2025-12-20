@extends('layouts.instructor')

@section('title', 'Dashboard')

@section('instructor-content')
<div class="page-header">
    <h2>System Overview</h2>
    <p>Welcome back, {{ auth()->user()->name }}! Here's what's happening with your courses.</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card stat-blue">
        <div class="stat-content">
            <p class="stat-label">My Courses</p>
            <div class="stat-value-row">
                <span class="stat-value">{{ $totalCourses }}</span>
                <i class="fas fa-book stat-icon"></i>
            </div>
            <p class="stat-footer">All courses</p>
        </div>
    </div>

    <div class="stat-card stat-green">
        <div class="stat-content">
            <p class="stat-label">Published Courses</p>
            <div class="stat-value-row">
                <span class="stat-value">{{ $publishedCourses }}</span>
                <i class="fas fa-check-circle stat-icon"></i>
            </div>
            <p class="stat-footer">Visible to students</p>
        </div>
    </div>

    <div class="stat-card stat-yellow">
        <div class="stat-content">
            <p class="stat-label">Pending Enrollments</p>
            <div class="stat-value-row">
                <span class="stat-value">{{ $pendingEnrollments }}</span>
                <i class="fas fa-user-clock stat-icon"></i>
            </div>
            <p class="stat-footer">Need approval</p>
        </div>
    </div>

    <div class="stat-card stat-purple">
        <div class="stat-content">
            <p class="stat-label">Total Students</p>
            <div class="stat-value-row">
                <span class="stat-value">{{ $totalStudents }}</span>
                <i class="fas fa-users stat-icon"></i>
            </div>
            <p class="stat-footer">Across all courses</p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1.5rem 0;">
    <a href="{{ route('instructor.courses.create') }}" class="quick-action-card">
        <i class="fas fa-plus-circle" style="font-size: 1.5rem; color: var(--blue-600); margin-bottom: 0.5rem;"></i>
        <h4>Create Course</h4>
        <p>Add new course</p>
    </a>
    
    <a href="{{ route('instructor.join-requests.index') }}" class="quick-action-card">
        <i class="fas fa-user-clock" style="font-size: 1.5rem; color: var(--yellow-600); margin-bottom: 0.5rem;"></i>
        <h4>Join Requests</h4>
        <p>{{ $pendingEnrollments }} pending</p>
    </a>
    
    <a href="{{ route('instructor.enrollments.index') }}" class="quick-action-card">
        <i class="fas fa-user-graduate" style="font-size: 1.5rem; color: var(--green-600); margin-bottom: 0.5rem;"></i>
        <h4>Enrollments</h4>
        <p>{{ $totalStudents }} total</p>
    </a>
    
    <a href="{{ route('instructor.analytics') }}" class="quick-action-card">
        <i class="fas fa-chart-line" style="font-size: 1.5rem; color: var(--purple-600); margin-bottom: 0.5rem;"></i>
        <h4>Analytics</h4>
        <p>View insights</p>
    </a>
</div>

<!-- Recent Courses -->
<div class="card">
    <div class="card-header flex-between">
        <div>
            <h3>Recent Courses</h3>
            <p>Latest course activity</p>
        </div>
        <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline">
            <i class="fas fa-list"></i>
            View All Courses
        </a>
    </div>
    <div class="card-content">
        <div id="recent-courses-list">
            @forelse ($recentCourses as $course)
            <div class="recent-course-item">
                <div class="recent-course-info">
                    <h4>{{ $course->title }}</h4>
                    <p>
                        {{ $course->category->name ?? 'Uncategorized' }}
                        • ${{ number_format($course->price, 2) }}
                        • {{ $course->enrollments_count ?? 0 }} students
                    </p>
                </div>

                <div class="recent-course-badges">
                    <span class="badge {{ $course->is_closed ? 'status-closed' : 'status-open' }}">
                        {{ $course->is_closed ? 'closed' : 'open' }}
                    </span>

                    <a href="{{ route('instructor.courses.show', $course->id) }}" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                        View
                    </a>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="fas fa-book" style="font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                <p class="empty-state-text">No courses found</p>
                <p class="empty-state-subtext">Create your first course to get started</p>
                <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    Create Course
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3>Recent Activity</h3>
        <p>Latest system notifications</p>
    </div>
    <div class="card-content">
        @if($recentNotifications->count() > 0)
            <div class="activity-list">
                @foreach($recentNotifications as $notification)
                <div class="activity-item">
                    <i class="fas fa-bell activity-icon {{ $notification->is_read ? 'blue' : 'yellow' }}"></i>
                    <div>
                        <p class="activity-text">{{ $notification->message }}</p>
                        <p class="activity-subtext">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-bell-slash" style="font-size: 2rem; color: var(--gray-400);"></i>
                <p class="empty-state-text">No recent notifications</p>
            </div>
        @endif
        
        @if($recentNotifications->count() > 0)
            <div style="text-align: center; margin-top: 1rem;">
                <a href="#" class="btn btn-outline" style="font-size: 0.875rem;">
                    View All Notifications
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Statistics Cards Hover Effects */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 1rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 
                    0 5px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .stat-blue:hover {
        border-color: #3b82f6;
    }
    
    .stat-green:hover {
        border-color: #10b981;
    }
    
    .stat-yellow:hover {
        border-color: #f59e0b;
    }
    
    .stat-purple:hover {
        border-color: #8b5cf6;
    }
    
    .stat-content {
        position: relative;
        z-index: 2;
    }
    
    .stat-label {
        color: #4b5563;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        transition: color 0.3s;
    }
    
    .stat-card:hover .stat-label {
        color: #111827;
    }
    
    .stat-value-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #111827;
        transition: transform 0.3s;
    }
    
    .stat-card:hover .stat-value {
        transform: scale(1.05);
    }
    
    .stat-icon {
        font-size: 2rem;
        opacity: 0.2;
        transition: opacity 0.3s;
    }
    
    .stat-card:hover .stat-icon {
        opacity: 0.3;
    }
    
    .stat-footer {
        color: #6b7280;
        font-size: 0.75rem;
        margin: 0;
    }
    
    /* Quick Action Cards */
    .quick-action-card {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 0.75rem;
        padding: 1.5rem;
        text-align: center;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .quick-action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        border-color: var(--blue-500);
    }
    
    .quick-action-card h4 {
        margin: 0.5rem 0;
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-900);
    }
    
    .quick-action-card p {
        margin: 0;
        font-size: 0.875rem;
        color: var(--gray-600);
    }
    
    /* Card Colors */
    .stat-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    
    .stat-blue .stat-label,
    .stat-blue .stat-value,
    .stat-blue .stat-footer {
        color: white;
    }
    
    .stat-blue .stat-icon {
        color: rgba(255, 255, 255, 0.3);
    }
    
    .stat-green {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .stat-green .stat-label,
    .stat-green .stat-value,
    .stat-green .stat-footer {
        color: white;
    }
    
    .stat-green .stat-icon {
        color: rgba(255, 255, 255, 0.3);
    }
    
    .stat-yellow {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .stat-yellow .stat-label,
    .stat-yellow .stat-value,
    .stat-yellow .stat-footer {
        color: white;
    }
    
    .stat-yellow .stat-icon {
        color: rgba(255, 255, 255, 0.3);
    }
    
    .stat-purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
    }
    
    .stat-purple .stat-label,
    .stat-purple .stat-value,
    .stat-purple .stat-footer {
        color: white;
    }
    
    .stat-purple .stat-icon {
        color: rgba(255, 255, 255, 0.3);
    }
    
    /* Recent Courses Card */
    .card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }
    
    .recent-course-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        transition: all 0.2s;
    }
    
    .recent-course-item:last-child {
        border-bottom: none;
    }
    
    .recent-course-item:hover {
        background: #f9fafb;
        padding-left: 1.25rem;
        border-left: 3px solid #3b82f6;
    }
    
    .recent-course-info h4 {
        margin: 0 0 0.25rem 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
    }
    
    .recent-course-info p {
        margin: 0;
        font-size: 0.75rem;
        color: #6b7280;
    }
    
    .recent-course-badges {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-open {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    
    .status-closed {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    
    .status-active {
        background-color: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 2rem 1rem;
    }
    
    .empty-state i {
        font-size: 2rem;
        color: var(--gray-400);
        margin-bottom: 0.5rem;
    }
    
    .empty-state-text {
        color: var(--gray-600);
        margin-bottom: 0.5rem;
    }
    
    .empty-state-subtext {
        color: var(--gray-500);
        font-size: 0.875rem;
    }
    
    /* Activity List */
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .activity-item {
        display: flex;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--gray-50);
        border-radius: 0.5rem;
    }
    
    .activity-icon {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
        margin-top: 0.125rem;
    }
    
    .activity-icon.blue { color: var(--blue-500); }
    .activity-icon.green { color: var(--green-500); }
    .activity-icon.yellow { color: var(--yellow-600); }
    
    .activity-text {
        font-size: 0.875rem;
        color: var(--gray-900);
    }
    
    .activity-subtext {
        font-size: 0.75rem;
        color: var(--gray-600);
    }
    
    /* Flex Utilities */
    .flex-between {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
        }
        
        .recent-course-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .recent-course-badges {
            width: 100%;
            justify-content: flex-start;
        }
        
        .recent-course-item:hover {
            transform: none;
            padding-left: 1rem;
        }
        
        .quick-action-card {
            padding: 1rem;
        }
        
        .page-header h2 {
            font-size: 1.5rem;
        }
        
        .page-header p {
            font-size: 0.875rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Add click functionality to stat cards
    document.addEventListener('DOMContentLoaded', function() {
        // Stat cards click events
        const statCards = document.querySelectorAll('.stat-card');
        
        statCards.forEach(card => {
            card.addEventListener('click', function() {
                const cardType = this.classList[1]; // stat-blue, stat-green, etc.
                
                // Add click animation
                this.style.transform = 'translateY(-8px) scale(1.02)';
                
                setTimeout(() => {
                    this.style.transform = 'translateY(-5px)';
                }, 150);
                
                // Navigate based on card type
                switch(cardType) {
                    case 'stat-blue':
                        window.location.href = '{{ route("instructor.courses.index") }}';
                        break;
                    case 'stat-green':
                        window.location.href = '{{ route("instructor.courses.index") }}?status=published';
                        break;
                    case 'stat-yellow':
                        window.location.href = '{{ route("instructor.join-requests.index") }}';
                        break;
                    case 'stat-purple':
                        window.location.href = '{{ route("instructor.enrollments.index") }}';
                        break;
                }
            });
        });
        
        // Quick action card hover effects
        const quickCards = document.querySelectorAll('.quick-action-card');
        quickCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>
@endpush