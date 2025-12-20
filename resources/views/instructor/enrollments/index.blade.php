@extends('layouts.instructor')

@section('title', 'Enrollments')

@section('instructor-content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Enrollments</h2>
            <p>Manage student enrollments in your courses</p>
        </div>
        <div class="flex" style="gap: 0.5rem;">
            <a href="{{ route('instructor.dashboard') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card stat-blue">
        <div class="stat-content">
            <p class="stat-label">Total Enrollments</p>
            <div class="stat-value-row">
                <span class="stat-value">{{ $totalEnrollments }}</span>
                <i class="fas fa-users stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Enrollments</h3>
        <p>Manage and track student progress</p>
    </div>
    
    <div class="card-content">
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if($enrollments->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Enrollment Date</th>
                            <th>Progress</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollments as $enrollment)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--gray-200); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user" style="color: var(--gray-600);"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $enrollment->student->name }}</strong>
                                        <div style="font-size: 0.75rem; color: var(--gray-600);">
                                            {{ $enrollment->student->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-weight: 500;">{{ $enrollment->course->title }}</td>
                            <td>{{ $enrollment->enrolled_at->format('M d, Y') }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="flex: 1; background: var(--gray-200); height: 6px; border-radius: 3px; overflow: hidden;">
                                        <div style="width: {{ $enrollment->progress }}%; background: var(--green-500); height: 100%;"></div>
                                    </div>
                                    <span style="font-size: 0.75rem; font-weight: 500; min-width: 2rem;">{{ $enrollment->progress }}%</span>
                                </div>
                            </td>
                            <td class="text-right">
                                <div class="table-actions">
                                    <form action="{{ route('instructor.enrollments.remove', $enrollment->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this student from the course?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn" title="Remove">
                                            <i class="fas fa-user-minus" style="color: #ef4444;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                {{ $enrollments->links('vendor.pagination.custom') }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-user-graduate" style="font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                <p>No enrollments found</p>
                <p style="color: var(--gray-500); font-size: 0.875rem;">Students will appear here when they enroll in your courses</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .progress-bar {
        width: 100%;
        height: 6px;
        background: var(--gray-200);
        border-radius: 3px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: var(--green-500);
        border-radius: 3px;
        transition: width 0.3s ease;
    }
</style>
@endpush