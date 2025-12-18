@extends('layouts.admin')

@section('title', 'Course Details - ' . $course->title)

@section('admin-content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Course Details</h2>
            <p>View and manage course information</p>
        </div>
        <div>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Back to Courses
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content">
        <div class="course-detail-content">
            <div class="course-header">
                <div class="course-image-placeholder">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="course-info">
                    <h3>{{ $course->title }}</h3>
                    <div class="course-meta">
                        <span><i class="fas fa-user"></i> Instructor: <strong>{{ $course->instructor->name ?? 'N/A' }}</strong></span>
                        <span><i class="fas fa-folder"></i> Category: <strong>{{ $course->category->name ?? 'N/A' }}</strong></span>
                        <span><i class="fas fa-dollar-sign"></i> Price: <strong>${{ number_format($course->price, 2) }}</strong></span>
                    </div>
                </div>
            </div>
            
            <div class="course-stats">
                <div class="stat-box">
                    <i class="fas fa-users"></i>
                    <div>
                        <span class="stat-number">{{ $course->enrollments_count ?? 0 }}</span>
                        <span class="stat-label">Enrollments</span>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-star"></i>
                    <div>
                        <span class="stat-number">{{ $course->average_rating ?? '0.0' }}</span>
                        <span class="stat-label">Rating</span>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-clock"></i>
                    <div>
                        <span class="stat-number">{{ $course->duration ?? 'N/A' }}</span>
                        <span class="stat-label">Weeks</span>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-video"></i>
                    <div>
                        <span class="stat-number">{{ $course->lessons_count ?? 0 }}</span>
                        <span class="stat-label">Lessons</span>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h4>Course Information</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Course ID:</label>
                        <span>{{ $course->id }}</span>
                    </div>
                    <div class="info-item">
                        <label>Status:</label>
                        @if($course->is_closed)
                            <span class="badge status-archived">Closed</span>
                        @else
                            <span class="badge status-active">Active</span>
                        @endif
                    </div>
                    <div class="info-item">
                        <label>Created:</label>
                        <span>{{ $course->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="info-item">
                        <label>Last Updated:</label>
                        <span>{{ $course->updated_at->format('M d, Y') }}</span>
                    </div>
                    <div class="info-item">
                        <label>Enrollment Status:</label>
                        @if($course->is_closed)
                            <span class="badge status-archived">Closed</span>
                        @else
                            <span class="badge status-open">Open</span>
                        @endif
                    </div>
                    <div class="info-item">
                        <label>Difficulty:</label>
                        <span>{{ $course->difficulty ?? 'Intermediate' }}</span>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h4>Description</h4>
                <p>{{ $course->description }}</p>
            </div>
            
            <div class="section">
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit Course
                    </a>
                    <form action="{{ route('admin.courses.delete', $course->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this course?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline" style="color: #ef4444; border-color: #ef4444;">
                            <i class="fas fa-trash"></i>
                            Delete Course
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
    .course-detail-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .course-header {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
    }
    
    .course-image-placeholder {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }
    
    .course-info {
        flex: 1;
    }
    
    .course-info h3 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .course-meta {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .course-meta span {
        font-size: 0.875rem;
        color: var(--gray-600);
    }
    
    .course-meta strong {
        color: var(--gray-900);
    }
    
    .course-meta i {
        width: 1rem;
        margin-right: 0.5rem;
        color: var(--blue-500);
    }
    
    .course-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
    }
    
    .stat-box {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: 0.5rem;
    }
    
    .stat-box i {
        font-size: 1.5rem;
        color: var(--blue-500);
    }
    
    .stat-number {
        display: block;
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--gray-900);
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: var(--gray-600);
    }
    
    .section {
        border-top: 1px solid var(--gray-200);
        padding-top: 1.5rem;
    }
    
    .section h4 {
        font-size: 1.125rem;
        margin-bottom: 0.75rem;
    }
    
    .section p {
        color: var(--gray-700);
        line-height: 1.6;
    }
    
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
    
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-active {
        background-color: #dcfce7;
        color: #166534;
    }
    
    .status-archived {
        background-color: #e5e7eb;
        color: #374151;
    }
    
    .status-open {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    @media (max-width: 768px) {
        .course-header {
            flex-direction: column;
            text-align: center;
        }
        
        .course-meta {
            align-items: center;
        }
        
        .course-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush