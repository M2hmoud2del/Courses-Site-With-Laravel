@extends('layouts.admin')

@section('title', 'Edit Course - ' . $course->title)

@section('admin-content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Edit Course</h2>
            <p>Update course information</p>
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
        <form action="{{ route('admin.courses.update', $course->id) }}" method="POST" style="max-width: 800px; margin: 0 auto;">
            @csrf
            @method('PUT')
            
            <div class="course-header" style="margin-bottom: 2rem;">
                <div class="course-image-placeholder" style="width: 80px; height: 80px; font-size: 2rem;">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="course-info">
                    <h3>{{ $course->title }}</h3>
                    <p style="color: var(--gray-600); font-size: 0.875rem;">ID: {{ $course->id }}</p>
                </div>
            </div>
            
            <div class="form-group">
                <label for="title">Course Title *</label>
                <input type="text" id="title" name="title" class="form-input" value="{{ old('title', $course->title) }}" required>
                @error('title') <span class="error-text">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group">
                <label for="description">Course Description *</label>
                <textarea id="description" name="description" class="form-input" rows="4" required>{{ old('description', $course->description) }}</textarea>
                @error('description') <span class="error-text">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="instructor_id">Instructor *</label>
                    <select id="instructor_id" name="instructor_id" class="form-input" required>
                        <option value="">Select instructor</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}" {{ (old('instructor_id', $course->instructor_id) == $instructor->id) ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('instructor_id') <span class="error-text">{{ $message }}</span> @enderror
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" class="form-input" required>
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (old('category_id', $course->category_id) == $category->id) ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="error-text">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="price">Price ($) *</label>
                    <input type="number" id="price" name="price" class="form-input" value="{{ old('price', $course->price) }}" step="0.01" required>
                    @error('price') <span class="error-text">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div class="form-group">
                <label>Enrollment Mode</label>
                <div class="toggle-buttons" style="margin-top: 0.5rem;">
                    <button type="button" class="toggle-btn enrollment-btn {{ !$course->is_closed ? 'active' : '' }}" onclick="setEnrollmentMode(false)">
                        <i class="fas fa-user-plus"></i>
                        Direct Join
                    </button>
                    <button type="button" class="toggle-btn enrollment-btn {{ $course->is_closed ? 'active' : '' }}" onclick="setEnrollmentMode(true)">
                        <i class="fas fa-user-clock"></i>
                        Request Approval
                    </button>
                </div>
                <div class="mode-description">
                    <span id="direct-mode-desc" style="display: {{ !$course->is_closed ? 'block' : 'none' }};">
                        <i class="fas fa-check-circle" style="color: #10b981;"></i>
                        Students can join the course directly without approval
                    </span>
                    <span id="request-mode-desc" style="display: {{ $course->is_closed ? 'block' : 'none' }};">
                        <i class="fas fa-clock" style="color: #f59e0b;"></i>
                        Students must send join requests that require instructor approval
                    </span>
                </div>
                <input type="hidden" id="is_closed" name="is_closed" value="{{ old('is_closed', $course->is_closed) }}">
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 2rem;">
                <a href="{{ route('admin.courses.show', $course->id) }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
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
        
        --green-50: #f0fdf4;
        --green-100: #dcfce7;
        --green-500: #10b981;
        --green-600: #059669;
        
        --yellow-50: #fefce8;
        --yellow-100: #fef3c7;
        --yellow-500: #f59e0b;
        --yellow-600: #d97706;
        
        --red-500: #ef4444;
    }
    
    .course-header {
        display: flex;
        gap: 1rem;
        align-items: center;
    }
    
    .course-image-placeholder {
        background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    .course-info h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
    }
    
    .form-input {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
        background: white;
        color: var(--gray-800);
    }
    
    textarea.form-input {
        resize: vertical;
        min-height: 100px;
    }
    
    select.form-input {
        cursor: pointer;
    }
    
    .form-input:focus {
        outline: none;
        border-color: var(--blue-500);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .error-text {
        color: var(--red-500);
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
    }
    
    .toggle-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .toggle-btn.enrollment-btn {
        padding: 1rem;
        border: 2px solid var(--gray-300);
        background: white;
        border-radius: 0.5rem;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        color: var(--gray-700);
    }
    
    .toggle-btn.enrollment-btn:hover {
        border-color: var(--gray-400);
        background: var(--gray-50);
        transform: translateY(-1px);
    }
    
    .toggle-btn.enrollment-btn.active {
        border-color: transparent;
        font-weight: 600;
    }
    
    .toggle-btn.enrollment-btn.active:first-child {
        background: var(--green-100);
        color: var(--green-600);
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.1);
    }
    
    .toggle-btn.enrollment-btn.active:last-child {
        background: var(--yellow-100);
        color: var(--yellow-600);
        box-shadow: 0 2px 4px rgba(245, 158, 11, 0.1);
    }
    
    .toggle-btn.enrollment-btn i {
        font-size: 1.25rem;
    }
    
    .mode-description {
        margin-top: 0.75rem;
        padding: 0.75rem;
        background: var(--gray-50);
        border-radius: 0.5rem;
        border: 1px solid var(--gray-200);
        font-size: 0.875rem;
        color: var(--gray-700);
    }
    
    .mode-description span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .mode-description i {
        font-size: 1rem;
        flex-shrink: 0;
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
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .toggle-buttons {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial enrollment mode
        const isClosed = document.getElementById('is_closed').value === '1';
        setEnrollmentMode(isClosed);
    });
    
    function setEnrollmentMode(isClosed) {
        const buttons = document.querySelectorAll('.enrollment-btn');
        const hiddenInput = document.getElementById('is_closed');
        const directDesc = document.getElementById('direct-mode-desc');
        const requestDesc = document.getElementById('request-mode-desc');
        
        buttons.forEach(btn => btn.classList.remove('active'));
        
        if (isClosed) {
            buttons[1].classList.add('active');
            hiddenInput.value = '1';
            directDesc.style.display = 'none';
            requestDesc.style.display = 'flex';
        } else {
            buttons[0].classList.add('active');
            hiddenInput.value = '0';
            directDesc.style.display = 'flex';
            requestDesc.style.display = 'none';
        }
    }
</script>
@endpush