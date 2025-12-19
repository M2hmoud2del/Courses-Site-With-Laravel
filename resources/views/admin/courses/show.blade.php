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
            
            @php
                // حساب الإحصائيات من البيانات الحقيقية
                $enrollmentsCount = $course->enrollments()->count();
                $pendingRequests = $course->joinRequests()->where('status', 'PENDING')->count();
            @endphp
            
            <div class="course-stats">
                <div class="stat-box">
                    <i class="fas fa-users"></i>
                    <div>
                        <span class="stat-number">{{ $enrollmentsCount }}</span>
                        <span class="stat-label">Enrolled Students</span>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-clock"></i>
                    <div>
                        <span class="stat-number">{{ $pendingRequests }}</span>
                        <span class="stat-label">Pending Requests</span>
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
                        <label>Enrollment Mode:</label>
                        @if($course->is_closed)
                            <span class="badge status-request">
                                <i class="fas fa-user-clock"></i>
                                Request Approval
                            </span>
                        @else
                            <span class="badge status-direct">
                                <i class="fas fa-user-plus"></i>
                                Direct Join
                            </span>
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
        
        --blue-500: #3b82f6;
        --blue-600: #2563eb;
        
        --green-500: #10b981;
        --green-600: #059669;
        
        --yellow-500: #f59e0b;
        --yellow-600: #d97706;
        
        --red-500: #ef4444;
    }
    
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
        font-weight: 600;
        color: var(--gray-900);
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
        display: flex;
        align-items: center;
    }
    
    .course-meta strong {
        color: var(--gray-900);
        font-weight: 500;
        margin-left: 0.25rem;
    }
    
    .course-meta i {
        width: 1rem;
        margin-right: 0.5rem;
        color: var(--blue-500);
    }
    
    .course-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        max-width: 350px;
    }
    
    .stat-box {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: 0.5rem;
        border: 1px solid var(--gray-200);
        transition: all 0.2s;
    }
    
    .stat-box:hover {
        background: var(--gray-100);
        transform: translateY(-2px);
    }
    
    .stat-box i {
        font-size: 1.5rem;
        color: var(--blue-500);
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
    
    .info-item span {
        font-size: 0.875rem;
        color: var(--gray-800);
    }
    
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        border: 1px solid transparent;
        width: fit-content;
    }
    
    .status-direct {
        background-color: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }
    
    .status-request {
        background-color: #fef3c7;
        color: #92400e;
        border-color: #fde68a;
    }
    
    .status-direct i {
        color: #059669;
        font-size: 0.75rem;
    }
    
    .status-request i {
        color: #d97706;
        font-size: 0.75rem;
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
            max-width: 100%;
        }
        
        .section > div:last-child {
            flex-direction: column;
        }
        
        .section > div:last-child .btn {
            width: 100%;
        }
    }
</style>
@endpush