@extends('layouts.admin')

@section('title', 'Dashboard')

@section('admin-content')
<div class="page-header">
    <h2>System Overview</h2>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card stat-blue">
        <div class="stat-content">
            <p class="stat-label">Total Users</p>
            <div class="stat-value-row">
                <span id="stat-users" class="stat-value">{{ $totalUsers }}</span>
                <i class="fas fa-users stat-icon"></i>
            </div>
            <p class="stat-footer">Platform-wide</p>
        </div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-content">
            <p class="stat-label">Active Courses</p>
            <div class="stat-value-row">
                <span id="stat-courses" class="stat-value">{{ $activeCourses }}</span>
                <i class="fas fa-book-open stat-icon"></i>
            </div>
            <p class="stat-footer">Published & visible</p>
        </div>
    </div>
    <div class="stat-card stat-yellow">
        <div class="stat-content">
            <p class="stat-label">Pending Requests</p>
            <div class="stat-value-row">
                <span id="stat-requests" class="stat-value">{{ $pendingRequests }}</span>
                <i class="fas fa-clock stat-icon"></i>
            </div>
            <p class="stat-footer">Awaiting review</p>
        </div>
    </div>
    <div class="stat-card stat-purple">
        <div class="stat-content">
            <p class="stat-label">Total Enrollments</p>
            <div class="stat-value-row">
                <span id="stat-enrollments" class="stat-value">{{ $totalEnrollments }}</span>
                <i class="fas fa-chart-bar stat-icon"></i>
            </div>
            <p class="stat-footer">All time</p>
        </div>
    </div>
</div>

<!-- Recent Courses -->
<div class="card">
    <div class="card-header">
        <h3>Recent Courses</h3>
        <p>Latest course activity</p>
    </div>
    <div class="card-content">
        <div id="recent-courses-list">
            @forelse ($recentCourses as $course)
            <div class="recent-course-item">
                <div class="recent-course-info">
                    <h4>{{ $course->title }}</h4>
                    <p>
                        by {{ $course->instructor->name }}
                        • {{ $course->category->name }}
                    </p>
                </div>

                <div class="recent-course-badges">
                    <span class="badge {{ $course->is_closed ? 'status-closed' : 'status-open' }}">
                        {{ $course->is_closed ? 'closed' : 'open' }}
                    </span>

                    <span class="badge status-active">
                        active
                    </span>
                </div>
            </div>
            @empty
            <p class="text-muted">No courses found.</p>
            @endforelse
        </div>
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
    
    /* Recent Courses Card Hover */
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
                        window.location.href = '{{ route("admin.users.index") }}';
                        break;
                    case 'stat-green':
                        window.location.href = '{{ route("admin.courses.index") }}';
                        break;
                    case 'stat-yellow':
                        // You can add navigation for pending requests
                        console.log('Navigate to pending requests');
                        break;
                    case 'stat-purple':
                        // You can add navigation for enrollments
                        console.log('Navigate to enrollments');
                        break;
                }
            });
        });
    });
</script>
@endpush