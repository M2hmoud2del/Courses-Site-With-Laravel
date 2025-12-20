@extends('layouts.instructor')

@section('title', 'Course Management')

@section('instructor-content')
<div class="page-header">
    <h2>Course Management</h2>
    <p>Oversee My courses on the platform</p>
</div>

<div class="card">
    <div class="card-header flex-between">
        <div>
            <h3>My Courses</h3>
            <p>Total: {{ $courses->total() }} courses</p>
        </div>
        <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i>
            Add Course
        </a>
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
        <!-- Courses Table -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Enrollment Mode</th>
                        <th>Students</th>
                        <th>Price</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $course)
                    @php
                        $enrollmentsCount = $course->enrollments()->count();
                    @endphp
                    <tr>
                        <td style="font-weight: 500;">{{ $course->title }}</td>
                        <td>{{ $course->category->name ?? 'N/A' }}</td>
                        <td>
                            @if($course->is_closed)
                                <span class="badge status-request">
                                    <i class="fas fa-user-clock"></i>
                                    Request
                                </span>
                            @else
                                <span class="badge status-direct">
                                    <i class="fas fa-user-plus"></i>
                                    Direct
                                </span>
                            @endif
                        </td>
                        <td>{{ $enrollmentsCount }}</td>
                        <td>${{ number_format($course->price, 2) }}</td>
                        <td class="text-right">
                            <div class="table-actions">
                                <a href="{{ route('admin.courses.show', $course->id) }}" class="icon-btn" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.courses.edit', $course->id) }}" class="icon-btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('instructor.courses.delete', $course->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this course?')">
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
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    
    .data-table thead {
        background: var(--gray-50);
    }
    
    .data-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--gray-700);
        border-bottom: 2px solid var(--gray-200);
        white-space: nowrap;
    }
    
    .data-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--gray-200);
        color: var(--gray-700);
        vertical-align: middle;
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
        border: 1px solid var(--gray-300);
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
        border-color: var(--gray-400);
        transform: translateY(-1px);
    }
    
    .text-right {
        text-align: right;
    }
    
    /* Badge Styles */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        border: 1px solid transparent;
        white-space: nowrap;
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
    
    .badge i {
        font-size: 0.7rem;
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
    
    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
    }
    
    .card-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
    }
    
    .card-header p {
        margin: 0.25rem 0 0 0;
        color: var(--gray-600);
        font-size: 0.875rem;
    }
    
    .card-content {
        padding: 1.5rem;
    }
    
    .flex-between {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    /* Page Header */
    .page-header {
        margin-bottom: 2rem;
    }
    
    .page-header h2 {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }
    
    .page-header p {
        color: var(--gray-600);
        font-size: 1rem;
    }
    
    /* Pagination */
    .pagination {
        margin-top: 1.5rem;
    }
    
    .pagination .flex {
        display: flex;
        gap: 0.5rem;
        list-style: none;
        padding: 0;
        justify-content: center;
        align-items: center;
    }
    
    .pagination .page-item {
        display: inline-block;
    }
    
    .pagination .page-link {
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        background: white;
        color: #374151;
        text-decoration: none;
        border-radius: 0.375rem;
        transition: all 0.2s;
        font-size: 0.875rem;
        min-width: 2.5rem;
        text-align: center;
        display: inline-block;
    }
    
    .pagination .page-link:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }
    
    .pagination .active .page-link {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
    
    .pagination .disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f9fafb;
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
    
    /* Responsive */
    @media (max-width: 768px) {
        .data-table {
            font-size: 0.75rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 0.5rem;
        }
        
        .badge {
            font-size: 0.65rem;
            padding: 3px 6px;
        }
        
        .icon-btn {
            width: 1.75rem;
            height: 1.75rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
        }
    }
    .alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

    .alert-success {
        background-color: #dcfce7;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .alert-danger {
        background-color: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .alert i {
        font-size: 1rem;
    }

    .alert-success i {
        color: #059669;
    }

    .alert-danger i {
        color: #dc2626;
    }
</style>
@endpush    