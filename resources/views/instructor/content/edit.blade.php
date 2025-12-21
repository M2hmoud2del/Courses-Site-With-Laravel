@extends('layouts.instructor')

@section('title', 'Edit Content - ' . $content->title)

@section('instructor-content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Edit Content</h2>
            <p>Update content in {{ $course->title }}</p>
        </div>
        <div>
            <a href="{{ route('instructor.content.index', $course->id) }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Back to Content
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content">
        <form action="{{ route('instructor.content.update', [$course->id, $content->id]) }}" method="POST" style="max-width: 800px; margin: 0 auto;">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="content_type">Content Type *</label>
                <select id="content_type" name="content_type" class="form-input" required onchange="toggleContentFields()">
                    <option value="">Select content type</option>
                    <option value="LESSON" {{ old('content_type', $content->content_type) == 'LESSON' ? 'selected' : '' }}>Lesson (Text Content)</option>
                    <option value="VIDEO" {{ old('content_type', $content->content_type) == 'VIDEO' ? 'selected' : '' }}>Video (YouTube/Vimeo Link)</option>
                    <option value="DOCUMENT" {{ old('content_type', $content->content_type) == 'DOCUMENT' ? 'selected' : '' }}>Document (Google Drive/PDF Link)</option>
                    <option value="LINK" {{ old('content_type', $content->content_type) == 'LINK' ? 'selected' : '' }}>External Link</option>
                </select>
                @error('content_type') <span class="error-text">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" class="form-input" 
                       value="{{ old('title', $content->title) }}" 
                       placeholder="Enter content title" 
                       required>
                @error('title') <span class="error-text">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-input" 
                          rows="3" placeholder="Brief description of this content">{{ old('description', $content->description) }}</textarea>
                @error('description') <span class="error-text">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group" id="external_link_field">
                <label for="external_link">External Link *</label>
                <input type="url" id="external_link" name="external_link" class="form-input" 
                       value="{{ old('external_link', $content->external_link) }}" 
                       placeholder="https://www.youtube.com/watch?v=... or https://drive.google.com/file/d/...">
                <div class="field-help">
                    <i class="fas fa-info-circle"></i>
                    <span id="link_help_text">Enter the full URL to your content</span>
                </div>
                @error('external_link') <span class="error-text">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group" id="content_field">
                <label for="content">Content *</label>
                <textarea id="content" name="content" class="form-input" 
                          rows="8" placeholder="Enter your lesson content here">{{ old('content', $content->content) }}</textarea>
                @error('content') <span class="error-text">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group">
                <label for="order">Display Order *</label>
                <input type="number" id="order" name="order" class="form-input" 
                       value="{{ old('order', $content->order + 1) }}" 
                       min="0" required>
                <div class="field-help">
                    <i class="fas fa-info-circle"></i>
                    Determines the order in which this content appears to students
                </div>
                @error('order') <span class="error-text">{{ $message }}</span> @enderror
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 2rem;">
                <a href="{{ route('instructor.content.index', $course->id) }}" class="btn btn-outline">Cancel</a>
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
    
    .field-help {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        font-size: 0.75rem;
        color: var(--gray-600);
    }
    
    .field-help i {
        font-size: 0.875rem;
        color: var(--blue-500);
    }
    
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
        .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        toggleContentFields(); // Initialize based on selected value
    });
    
    function toggleContentFields() {
        const contentType = document.getElementById('content_type').value;
        const externalLinkField = document.getElementById('external_link_field');
        const contentField = document.getElementById('content_field');
        const linkHelpText = document.getElementById('link_help_text');
        
        if (['VIDEO', 'DOCUMENT', 'LINK'].includes(contentType)) {
            externalLinkField.style.display = 'block';
            contentField.style.display = 'none';
            
            // Update help text based on content type
            if (contentType === 'VIDEO') {
                linkHelpText.textContent = 'Enter YouTube or Vimeo video URL';
            } else if (contentType === 'DOCUMENT') {
                linkHelpText.textContent = 'Enter Google Drive, Dropbox, or direct PDF link';
            } else {
                linkHelpText.textContent = 'Enter the full URL to your external resource';
            }
        } else if (contentType === 'LESSON') {
            externalLinkField.style.display = 'none';
            contentField.style.display = 'block';
        }
    }
</script>
@endpush