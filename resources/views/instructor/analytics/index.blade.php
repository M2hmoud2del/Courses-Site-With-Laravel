@extends('layouts.instructor')

@section('title', 'Analytics')

@section('instructor-content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Analytics Dashboard</h2>
            <p>Track your course performance and student engagement</p>
        </div>
        <div class="flex" style="gap: 0.5rem;">
            <a href="{{ route('instructor.dashboard') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card stat-blue">
        <div class="stat-content">
            <p class="stat-label">Total Courses</p>
            <div class="stat-value-row">
                <span class="stat-value">{{ $coursesStats->total ?? 0 }}</span>
                <i class="fas fa-book stat-icon"></i>
            </div>
            <p class="stat-footer">{{ $coursesStats->open ?? 0 }} Open • {{ $coursesStats->closed ?? 0 }} Closed</p>
        </div>
    </div>

    <div class="stat-card stat-green">
        <div class="stat-content">
            <p class="stat-label">Enrollments</p>
            <div class="stat-value-row">
                <span class="stat-value">{{ $enrollmentStats->total ?? 0 }}</span>
                <i class="fas fa-user-graduate stat-icon"></i>
            </div>
            <p class="stat-footer">Avg Progress: {{ round($enrollmentStats->avg_progress ?? 0, 1) }}%</p>
        </div>
    </div>

    <div class="stat-card stat-yellow">
        <div class="stat-content">
            <p class="stat-label">Total Revenue</p>
            <div class="stat-value-row">
                <span class="stat-value">${{ number_format($totalRevenue, 2) }}</span>
                <i class="fas fa-dollar-sign stat-icon"></i>
            </div>
            <p class="stat-footer">Avg per course: ${{ number_format($avgRevenuePerCourse, 2) }}</p>
        </div>
    </div>

    <div class="stat-card stat-purple">
        <div class="stat-content">
            <p class="stat-label">Progress Range</p>
            <div class="stat-value-row">
                <span class="stat-value">{{ $enrollmentStats->min_progress ?? 0 }}-{{ $enrollmentStats->max_progress ?? 0 }}%</span>
                <i class="fas fa-chart-line stat-icon"></i>
            </div>
            <p class="stat-footer">Student progress range</p>
        </div>
    </div>
</div>

<!-- Two Column Layout -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Top Performing Courses -->
    <div class="card">
        <div class="card-header">
            <h3>Top Performing Courses</h3>
            <p>By number of enrollments</p>
        </div>
        <div class="card-content">
            @if($topCourses->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($topCourses as $course)
                    <div style="padding: 0.75rem; background: var(--gray-50); border-radius: 0.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <h4 style="margin: 0; font-size: 0.875rem; font-weight: 600;">{{ $course->title }}</h4>
                            <span class="badge status-active">{{ $course->enrollments_count }} students</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.75rem; color: var(--gray-600);">
                                Revenue: ${{ number_format($course->price * $course->enrollments_count, 2) }}
                            </span>
                            <span style="font-size: 0.75rem; color: var(--gray-600);">
                                ${{ number_format($course->price, 2) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-chart-bar" style="font-size: 2rem; color: var(--gray-400);"></i>
                    <p>No enrollment data available</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Enrollment Trends -->
    <div class="card">
        <div class="card-header">
            <h3>Recent Enrollment Trends</h3>
            <p>Last 6 months</p>
        </div>
        <div class="card-content">
            @if($monthlyEnrollments->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($monthlyEnrollments as $trend)
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <span style="font-size: 0.875rem; font-weight: 500;">
                                {{ date('F Y', mktime(0, 0, 0, $trend->month, 1, $trend->year)) }}
                            </span>
                            <span style="font-size: 0.875rem; font-weight: 600;">{{ $trend->count }}</span>
                        </div>
                        <div style="height: 4px; background: var(--gray-200); border-radius: 2px; overflow: hidden;">
                            <div style="width: {{ ($trend->count / max($monthlyEnrollments->max('count'), 1)) * 100 }}%; height: 100%; background: var(--blue-500);"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-chart-line" style="font-size: 2rem; color: var(--gray-400);"></i>
                    <p>No enrollment trends available</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Detailed Course List -->
<div class="card">
    <div class="card-header">
        <h3>Course Performance Details</h3>
        <p>Detailed breakdown of all your courses</p>
    </div>
    <div class="card-content">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Enrollments</th>
                        <th>Revenue</th>
                        <th>Avg Progress</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $course)
                    <tr>
                        <td style="font-weight: 500;">{{ $course->title }}</td>
                        <td>
                            @if($course->is_closed)
                                <span class="badge status-closed">Closed</span>
                            @else
                                <span class="badge status-open">Open</span>
                            @endif
                        </td>
                        <td>{{ $course->enrollments_count }}</td>
                        <td>${{ number_format($course->price * $course->enrollments_count, 2) }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span>{{ round($course->enrollments_avg_progress ?? 0, 1) }}%</span>
                                <div style="flex: 1; max-width: 100px; background: var(--gray-200); height: 4px; border-radius: 2px; overflow: hidden;">
                                    <div style="width: {{ $course->enrollments_avg_progress ?? 0 }}%; height: 100%; background: var(--green-500);"></div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $course->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .empty-state {
        text-align: center;
        padding: 2rem 1rem;
    }
    
    .empty-state i {
        font-size: 2rem;
        color: var(--gray-400);
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        margin: 0;
        color: var(--gray-600);
    }
    
    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .card-content {
            padding: 1rem;
        }
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .card-content {
            padding: 0.75rem;
        }
        
        .data-table {
            font-size: 0.75rem;
        }
    }
</style>
@endpush