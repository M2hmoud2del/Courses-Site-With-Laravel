@extends('layouts.admin')

@section('title', 'Platform Statistics')

@section('admin-content')
<div class="page-header">
    <h2>Platform Statistics</h2>
    <p>Overview of system performance and usage</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card stat-blue">
        <div class="stat-content">
            <p class="stat-label">Total Users</p>
            <div class="stat-value-row">
                <span class="stat-value">{{ $totalUsers ?? 0 }}</span>
                <i class="fas fa-users stat-icon"></i>
            </div>
            <p class="stat-footer">Platform-wide</p>
        </div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-content">
            <p class="stat-label">Total Courses</p>
            <div class="stat-value-row">
                <span class="stat-value">{{ $courses ?? 0 }}</span>
                <i class="fas fa-book-open stat-icon"></i>
            </div>
            <p class="stat-footer">All categories</p>
        </div>
    </div>
    <div class="stat-card stat-yellow">
        <div class="stat-content">
            <p class="stat-label">Active Students</p>
            <div class="stat-value-row">
                <span class="stat-value">{{ $students ?? 0 }}</span>
                <i class="fas fa-user-graduate stat-icon"></i>
            </div>
            <p class="stat-footer">Enrolled in courses</p>
        </div>
    </div>
    <div class="stat-card stat-purple">
        <div class="stat-content">
            <p class="stat-label">Active Instructors</p>
            <div class="stat-value-row">
                <span class="stat-value">{{ $instructors ?? 0 }}</span>
                <i class="fas fa-chalkboard-teacher stat-icon"></i>
            </div>
            <p class="stat-footer">Course creators</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Monthly Enrollments</h3>
        <p>Course enrollment trends over time</p>
    </div>
    <div class="card-content">
        <!-- Enrollment Chart Placeholder -->
        <div style="height: 300px; display: flex; align-items: center; justify-content: center; background: var(--gray-50); border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <div style="text-align: center;">
                <i class="fas fa-chart-line" style="font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                <p style="color: var(--gray-600);">Enrollment chart would appear here</p>
            </div>
        </div>
        
        <!-- Monthly Stats -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <span style="font-weight: 500; color: var(--gray-700);">Monthly Enrollments:</span>
                <span style="background: var(--blue-100); color: var(--blue-700); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">458</span>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <span style="font-weight: 500; color: var(--gray-700);">Avg. Rating:</span>
                <span style="background: var(--green-100); color: var(--green-700); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">4.7</span>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <span style="font-weight: 500; color: var(--gray-700);">Completion Rate:</span>
                <span style="background: var(--purple-100); color: var(--purple-700); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">85%</span>
            </div>
        </div>
    </div>
</div>

<div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
    <div class="card">
        <div class="card-header">
            <h3>Course Status Distribution</h3>
        </div>
        <div class="card-content">
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--green-600); font-weight: 500;">Active Courses</span>
                    <span style="background: var(--green-100); color: var(--green-700); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">
                        {{ $activeCourses ?? 0 }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--yellow-600); font-weight: 500;">Draft Courses</span>
                    <span style="background: var(--yellow-100); color: var(--yellow-700); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">
                        {{ $draftCourses ?? 0 }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--gray-600); font-weight: 500;">Closed Courses</span>
                    <span style="background: var(--gray-100); color: var(--gray-700); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">
                        {{ $closedCourses ?? 0 }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>System Performance</h3>
        </div>
        <div class="card-content">
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 500;">System Uptime</span>
                    <span style="color: var(--green-600); font-weight: 600;">98%</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 500;">Response Time</span>
                    <span style="color: var(--blue-600); font-weight: 600;">120ms</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 500;">Storage Usage</span>
                    <span style="color: var(--purple-600); font-weight: 600;">65%</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-card {
        border-radius: 0.75rem;
        padding: 1.5rem;
        color: white;
    }
    
    .stat-card.stat-blue {
        background: linear-gradient(135deg, var(--blue-500) 0%, var(--blue-600) 100%);
    }
    
    .stat-card.stat-green {
        background: linear-gradient(135deg, var(--green-500) 0%, var(--green-600) 100%);
    }
    
    .stat-card.stat-yellow {
        background: linear-gradient(135deg, var(--yellow-500) 0%, var(--yellow-600) 100%);
    }
    
    .stat-card.stat-purple {
        background: linear-gradient(135deg, var(--purple-500) 0%, var(--purple-600) 100%);
    }
    
    .stat-content {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .stat-label {
        opacity: 0.9;
        font-size: 0.875rem;
    }
    
    .stat-value-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 600;
    }
    
    .stat-icon {
        font-size: 2rem;
        opacity: 0.8;
    }
    
    .stat-footer {
        opacity: 0.9;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    
    @media (max-width: 768px) {
        .grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush