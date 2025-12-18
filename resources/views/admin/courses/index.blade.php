@extends('layouts.admin')

@section('title', 'Course Management')

@section('admin-content')
<div class="page-header">
    <h2>Course Management</h2>
    <p>Oversee all courses on the platform</p>
</div>

<div class="card">
    <div class="card-header flex-between">
        <div>
            <h3>All Courses</h3>
            <p>Total: {{ $courses->total() }} courses</p>
        </div>
        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i>
            Add Course
        </a>
    </div>
    
    <div class="card-content">
        <!-- Search Bar -->
        <div class="search-bar">
            <div class="input-with-icon">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search courses...">
            </div>
        </div>

        <!-- Courses Table -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Instructor</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Enrollments</th>
                        <th>Price</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $course)
                    <tr>
                        <td style="font-weight: 500;">{{ $course->title }}</td>
                        <td>{{ $course->instructor->name ?? 'N/A' }}</td>
                        <td>{{ $course->category->name ?? 'N/A' }}</td>
                        <td>
                            @if($course->is_closed)
                                <span class="badge status-archived">Closed</span>
                            @else
                                <span class="badge status-active">Active</span>
                            @endif
                        </td>
                        <td>{{ $course->enrollments_count ?? 0 }}</td>
                        <td>${{ number_format($course->price, 2) }}</td>
                        <td class="text-right">
                            <div class="table-actions">
                                <a href="{{ route('admin.courses.show', $course->id) }}" class="icon-btn" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.courses.edit', $course->id) }}" class="icon-btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.courses.delete', $course->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this course?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn" title="Delete">
                                        <i class="fas fa-trash" style="color: #ef4444;"></i>
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
            {{ $courses->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .data-table thead {
        background: var(--gray-50);
    }
    
    .data-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-700);
        border-bottom: 2px solid var(--gray-200);
    }
    
    .data-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--gray-200);
        font-size: 0.875rem;
    }
    
    .data-table tr:hover {
        background: var(--gray-50);
    }
    
    .table-responsive {
        overflow-x: auto;
        margin-bottom: 1.5rem;
    }
    
    .table-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    
    .icon-btn {
        background: transparent;
        border: none;
        width: 2rem;
        height: 2rem;
        border-radius: 0.375rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--gray-600);
        transition: all 0.2s;
        text-decoration: none;
    }
    
    .icon-btn:hover {
        background: var(--gray-100);
    }
    
    .text-right {
        text-align: right;
    }
    
    /* Badge Styles */
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
    
    /* Pagination Custom Styles */
    .pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1.5rem;
    }
    
    .pagination-btn {
        width: 2.5rem;
        height: 2.5rem;
        border: 1px solid var(--gray-300);
        background: white;
        border-radius: 0.375rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    
    .pagination-btn:hover:not(.disabled) {
        border-color: var(--blue-500);
        color: var(--blue-500);
    }
    
    .pagination-btn.active {
        background: var(--blue-600);
        color: white;
        border-color: var(--blue-600);
    }
    
    .pagination-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .pagination-ellipsis {
        padding: 0 0.5rem;
        color: var(--gray-500);
    }
</style>
@endpush