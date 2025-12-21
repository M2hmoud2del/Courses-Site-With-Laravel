@extends('layouts.instructor')

@section('title', $course->title . ' - Content')

@section('instructor-content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>{{ $course->title }}</h2>
            <p>Manage course content</p>
        </div>
        <div class="flex" style="gap: 0.5rem;">
            <a href="{{ route('instructor.courses.show', $course->id) }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Back to Course
            </a>
            <a href="{{ route('instructor.content.create', $course->id) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Add Content
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Course Content</h3>
        <p>Drag and drop to reorder content</p>
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

        @if($course->contents->count() > 0)
            <div id="content-list" class="content-list">
                @foreach($course->contents->sortBy('order') as $content)
                <div class="content-item" data-id="{{ $content->id }}">
                    <div class="content-item-header">
                        <div class="content-type-icon" style="background: var(--{{ $content->type_color }}-100); color: var(--{{ $content->type_color }}-600);">
                            <i class="{{ $content->type_icon }}"></i>
                        </div>
                        <div class="content-info">
                            <h4>{{ $content->title }}</h4>
                            <p>{{ $content->description }}</p>
                            <div class="content-meta">
                                <span class="badge badge-{{ $content->type_color }}">
                                    <i class="{{ $content->type_icon }}"></i>
                                    {{ $content->type_label }}
                                </span>
                                @if($content->external_link)
                                <span class="badge badge-gray">
                                    <i class="fas fa-external-link-alt"></i>
                                    {{ $content->platform }}
                                </span>
                                @endif
                                <span class="order-badge">Order: {{ $content->order + 1 }}</span>
                            </div>
                        </div>
                        <div class="content-actions">
                            <a href="{{ $content->external_link }}" target="_blank" class="icon-btn" title="View Link">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <a href="{{ route('instructor.content.edit', [$course->id, $content->id]) }}" class="icon-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('instructor.content.destroy', [$course->id, $content->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this content?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn" title="Delete">
                                    <i class="fas fa-trash" style="color: #ef4444;"></i>
                                </button>
                            </form>
                            <div class="drag-handle">
                                <i class="fas fa-grip-vertical"></i>
                            </div>
                        </div>
                    </div>
                    
                    @if($content->content_type === 'LESSON' && $content->content)
                    <div class="content-preview">
                        <div class="preview-header">
                            <span>Content Preview:</span>
                        </div>
                        <div class="preview-content">
                            {{ Str::limit($content->content, 200) }}
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <div style="margin-top: 1.5rem; padding: 1rem; background: var(--gray-50); border-radius: 0.5rem;">
                <p style="color: var(--gray-600); font-size: 0.875rem; margin: 0;">
                    <i class="fas fa-info-circle"></i>
                    Drag the grip icon (<i class="fas fa-grip-vertical"></i>) to reorder content. The order determines how content appears to students.
                </p>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-book-open" style="font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                <p class="empty-state-text">No content yet</p>
                <p class="empty-state-subtext">Add your first content item to get started</p>
                <a href="{{ route('instructor.content.create', $course->id) }}" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    Add Content
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .content-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .content-item {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 0.75rem;
        overflow: hidden;
        transition: all 0.2s;
    }
    
    .content-item:hover {
        border-color: var(--blue-300);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .content-item.dragging {
        opacity: 0.5;
        background: var(--gray-50);
    }
    
    .content-item-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
    }
    
    .content-type-icon {
        width: 48px;
        height: 48px;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .content-info {
        flex: 1;
        min-width: 0;
    }
    
    .content-info h4 {
        margin: 0 0 0.25rem 0;
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-900);
    }
    
    .content-info p {
        margin: 0 0 0.5rem 0;
        color: var(--gray-600);
        font-size: 0.875rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .content-meta {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .badge-blue {
        background: var(--blue-100);
        color: var(--blue-800);
        border: 1px solid var(--blue-200);
    }
    
    .badge-purple {
        background: var(--purple-100);
        color: var(--purple-800);
        border: 1px solid var(--purple-200);
    }
    
    .badge-green {
        background: var(--green-100);
        color: var(--green-800);
        border: 1px solid var(--green-200);
    }
    
    .badge-yellow {
        background: var(--yellow-100);
        color: var(--yellow-800);
        border: 1px solid var(--yellow-200);
    }
    
    .badge-gray {
        background: var(--gray-100);
        color: var(--gray-800);
        border: 1px solid var(--gray-200);
    }
    
    .order-badge {
        font-size: 0.75rem;
        color: var(--gray-600);
        background: var(--gray-100);
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }
    
    .content-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: 1px solid var(--gray-200);
        background: white;
        color: var(--gray-600);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        text-decoration: none;
    }
    
    .icon-btn:hover {
        background: var(--gray-50);
        color: var(--gray-900);
        border-color: var(--gray-300);
    }
    
    .drag-handle {
        cursor: grab;
        padding: 0.5rem;
        color: var(--gray-400);
        transition: color 0.2s;
    }
    
    .drag-handle:hover {
        color: var(--gray-600);
    }
    
    .drag-handle:active {
        cursor: grabbing;
    }
    
    .content-preview {
        border-top: 1px solid var(--gray-100);
        padding: 1rem;
        background: var(--gray-50);
    }
    
    .preview-header {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
    }
    
    .preview-content {
        font-size: 0.875rem;
        color: var(--gray-600);
        line-height: 1.5;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: var(--gray-400);
        margin-bottom: 1rem;
    }
    
    .empty-state-text {
        color: var(--gray-600);
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .empty-state-subtext {
        color: var(--gray-500);
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .content-item-header {
            flex-wrap: wrap;
        }
        
        .content-actions {
            width: 100%;
            justify-content: flex-end;
            margin-top: 0.5rem;
        }
        
        .content-meta {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const contentList = document.getElementById('content-list');
        
        if (contentList) {
            new Sortable(contentList, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'dragging',
                
                onEnd: function(evt) {
                    const contentItems = contentList.querySelectorAll('.content-item');
                    const order = Array.from(contentItems).map(item => item.getAttribute('data-id'));
                    
                    // Send reorder request to server
                    fetch('{{ route("instructor.content.reorder", $course->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order: order })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update order numbers in UI
                            contentItems.forEach((item, index) => {
                                const orderBadge = item.querySelector('.order-badge');
                                if (orderBadge) {
                                    orderBadge.textContent = `Order: ${index + 1}`;
                                }
                            });
                            
                            showToast('Content order updated successfully', 'success');
                        } else {
                            showToast('Failed to update order', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Failed to update order', 'error');
                    });
                }
            });
        }
    });
    
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast show ${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
</script>
@endpush