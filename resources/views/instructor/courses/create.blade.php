@extends('layouts.instructor')

@section('title', 'Add New Course')

@section('instructor-content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Add New Course</h2>
            <p>Create a new course for the platform</p>
        </div>
        <div>
            <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Back to Courses
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content">
        <form action="{{ route('instructor.courses.store') }}" method="POST" style="max-width: 800px; margin: 0 auto;">
            @csrf
            
            <div class="form-group">
                <label for="title">Course Title *</label>
                <input type="text" id="title" name="title" class="form-input" placeholder="Enter course title" value="{{ old('title') }}" required>
                @error('title') <span class="error-text">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group">
                <label for="description">Course Description *</label>
                <textarea id="description" name="description" class="form-input" rows="4" placeholder="Enter course description" required>{{ old('description') }}</textarea>
                @error('description') <span class="error-text">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" class="form-input" required>
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                    <input type="number" id="price" name="price" class="form-input" placeholder="Enter price" step="1" min="0" value="{{ old('price') }}" required>
                    @error('price') <span class="error-text">{{ $message }}</span> @enderror
                </div>
                
                <div class="form-group">
                    <label for="duration">Duration (weeks)</label>
                    <input type="number" id="duration" name="duration" class="form-input" placeholder="Enter duration in weeks" min="0" value="{{ old('duration') }}">
                </div>
            </div>
            
            <div class="form-group">
                <label for="is_closed">Course Status</label>
                <div class="toggle-buttons" style="margin-top: 0.5rem;">
                    <button type="button" class="toggle-btn" onclick="setCourseStatus(false)">Active</button>
                    <button type="button" class="toggle-btn" onclick="setCourseStatus(true)">Closed</button>
                </div>
                <input type="hidden" id="is_closed" name="is_closed" value="{{ old('is_closed', 0) }}">
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 2rem;">
                <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Create Course
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
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
        const isClosed = document.getElementById('is_closed').value;
        setCourseStatus(isClosed === '1');
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