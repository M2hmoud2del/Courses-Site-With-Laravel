@extends('layouts.instructor')

@section('title', $course->title)

@section('instructor-content')
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>{{ $course->title }}</h2>
                <p>Course details and management</p>
            </div>
            <div class="flex" style="gap: 0.5rem;">
                <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Back to Courses
                </a>
                <a href="{{ route('instructor.courses.edit', $course->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i>
                    Edit Course
                </a>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <!-- Course Details -->
        <div class="card">
            <div class="card-header">
                <h3>Course Information</h3>
            </div>
            <div class="card-content">
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.5rem; font-size: 1rem; font-weight: 600;">Description</h4>
                    <p style="color: var(--gray-700); line-height: 1.6;">{{ $course->description }}</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; color: var(--gray-600); margin-bottom: 0.25rem;">Category</label>
                        <p style="font-weight: 500;">{{ $course->category->name ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; color: var(--gray-600); margin-bottom: 0.25rem;">Price</label>
                        <p style="font-weight: 500;">${{ number_format($course->price, 2) }}</p>
                    </div>

                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; color: var(--gray-600); margin-bottom: 0.25rem;">Status</label>
                        <p>
                            @if ($course->is_closed)
                                <span class="badge status-closed">Closed</span>
                            @else
                                <span class="badge status-open">Open</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; color: var(--gray-600); margin-bottom: 0.25rem;">Created</label>
                        <p style="font-weight: 500;">{{ $course->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Stats -->
        <div class="card">
            <div class="card-header">
                <h3>Course Statistics</h3>
            </div>
            <div class="card-content">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; color: var(--gray-600); margin-bottom: 0.25rem;">Total
                            Students</label>
                        <p style="font-size: 1.5rem; font-weight: 700; color: var(--blue-600);">
                            {{ $course->enrollments->count() }}</p>
                    </div>

                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; color: var(--gray-600); margin-bottom: 0.25rem;">Average
                            Progress</label>
                        @php
                            $avgProgress = $course->enrollments->avg('progress') ?? 0;
                        @endphp
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <p style="font-size: 1.5rem; font-weight: 700; color: var(--green-600);">
                                {{ round($avgProgress, 1) }}%</p>
                            <div
                                style="flex: 1; background: var(--gray-200); height: 6px; border-radius: 3px; overflow: hidden;">
                                <div style="width: {{ $avgProgress }}%; height: 100%; background: var(--green-500);"></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; color: var(--gray-600); margin-bottom: 0.25rem;">Total
                            Revenue</label>
                        <p style="font-size: 1.5rem; font-weight: 700; color: var(--purple-600);">
                            ${{ number_format($course->price * $course->enrollments->count(), 2) }}
                        </p>
                    </div>

                    <div>
                        <a href="{{ route('instructor.courses.students', $course->id) }}" class="btn btn-primary"
                            style="width: 100%; margin-top: 1rem;">
                            <i class="fas fa-users"></i>
                            View Students
                        </a>
                    </div>
                    <div style="margin-top: 1rem;">
                        <a href="{{ route('instructor.content.index', $course->id) }}" class="btn btn-primary"
                            style="width: 100%;">
                            <i class="fas fa-file-alt"></i>
                            Manage Course Content
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card">
        <div class="card-header">
            <h3>Enrolled Students</h3>
            <p>Students currently enrolled in this course</p>
        </div>
        <div class="card-content">
            @if ($course->enrollments->count() > 0)
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Enrollment Date</th>
                                <th>Progress</th>
                                <th>Last Activity</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($course->enrollments as $enrollment)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div
                                                style="width: 32px; height: 32px; border-radius: 50%; background: var(--gray-200); display: flex; align-items: center; justify-content: center;">
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
                                    <td>{{ $enrollment->enrolled_at->format('M d, Y') }}</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div
                                                style="flex: 1; background: var(--gray-200); height: 6px; border-radius: 3px; overflow: hidden;">
                                                <div
                                                    style="width: {{ $enrollment->progress }}%; height: 100%; background: var(--green-500);">
                                                </div>
                                            </div>
                                            <span
                                                style="font-size: 0.75rem; font-weight: 500; min-width: 2rem;">{{ $enrollment->progress }}%</span>
                                        </div>
                                    </td>
                                    <td>{{ $enrollment->updated_at->format('M d, Y') }}</td>
                                    <td class="text-right">
                                        <div class="table-actions">
                                            <button type="button" class="icon-btn" title="Send Message"
                                                onclick="alert('Message feature coming soon')">
                                                <i class="fas fa-envelope" style="color: var(--blue-500);"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-user-graduate"
                        style="font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                    <p>No students enrolled yet</p>
                    <p style="color: var(--gray-500); font-size: 0.875rem;">Students will appear here when they enroll in
                        this course</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .course-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-box {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
        }

        .stat-box .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--blue-600);
            margin-bottom: 0.25rem;
        }

        .stat-box .label {
            font-size: 0.875rem;
            color: var(--gray-600);
        }
    </style>
@endpush
