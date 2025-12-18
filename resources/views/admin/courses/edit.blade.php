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
                
                <div class="form-group">
                    <label for="duration">Duration (weeks)</label>
                    <input type="number" id="duration" name="duration" class="form-input" value="{{ old('duration', $course->duration) }}">
                </div>
            </div>
            
            <div class="form-group">
                <label for="is_closed">Course Status</label>
                <div class="toggle-buttons" style="margin-top: 0.5rem;">
                    <button type="button" class="toggle-btn {{ !$course->is_closed ? 'active' : '' }}" onclick="setCourseStatus(false)">Active</button>
                    <button type="button" class="toggle-btn {{ $course->is_closed ? 'active' : '' }}" onclick="setCourseStatus(true)">Closed</button>
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
    }
    
    textarea.form-input {
        resize: vertical;
    }
    
    .form-input:focus {
        outline: none;
        border-color: var(--blue-500);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .error-text {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
    }
    
    .toggle-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .toggle-btn {
        padding: 0.5rem 1rem;
        border: 1px solid var(--gray-300);
        background: white;
        border-radius: 0.5rem;
        cursor: pointer;
        font-size: 0.875rem;
        transition: all 0.2s;
        flex: 1;
    }
    
    .toggle-btn:hover {
        border-color: var(--gray-400);
    }
    
    .toggle-btn.active {
        background: var(--blue-600);
        color: white;
        border-color: var(--blue-600);
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial status
        const isClosed = document.getElementById('is_closed').value === '1';
        setCourseStatus(isClosed);
    });
    
    function setCourseStatus(isClosed) {
        const buttons = document.querySelectorAll('.toggle-btn');
        const hiddenInput = document.getElementById('is_closed');
        
        buttons.forEach(btn => btn.classList.remove('active'));
        
        if (isClosed) {
            buttons[1].classList.add('active');
            hiddenInput.value = '1';
        } else {
            buttons[0].classList.add('active');
            hiddenInput.value = '0';
        }
    }
</script>
@endpush