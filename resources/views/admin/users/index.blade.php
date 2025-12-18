@extends('layouts.admin')

@section('title', 'User Management')

@section('admin-content')
<div class="page-header">
    <h2>User Management</h2>
    <p>Manage all platform users and their roles</p>
</div>

<div class="card">
    <div class="card-header flex-between">
        <div>
            <h3>All Users</h3>
            <p>
                Total: {{ $totalUsers }} users
                ({{ $students }} Students,
                {{ $instructors }} Instructors,
                {{ $admins }} Admins)
            </p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus"></i>
            Add User
        </a>
    </div>
    
    <div class="card-content">
        <!-- Search Bar -->
        <div class="search-bar">
            <div class="input-with-icon">
                <i class="fas fa-search"></i>
                <input type="text" id="searchUsers" placeholder="Search users...">
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTable">
                    @foreach($users as $user)
                    <tr>
                        <td style="font-weight: 500;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                @php
                                    // Determine avatar color based on role
                                    $avatarColors = [
                                        'STUDENT' => ['bg' => '#e0f2fe', 'icon' => '#0369a1'],
                                        'INSTRUCTOR' => ['bg' => '#f3e8ff', 'icon' => '#7c3aed'],
                                        'ADMIN' => ['bg' => '#fee2e2', 'icon' => '#dc2626'],
                                        'student' => ['bg' => '#e0f2fe', 'icon' => '#0369a1'],
                                        'instructor' => ['bg' => '#f3e8ff', 'icon' => '#7c3aed'],
                                        'admin' => ['bg' => '#fee2e2', 'icon' => '#dc2626'],
                                    ];
                                    
                                    $role = strtoupper($user->role);
                                    $color = $avatarColors[$role] ?? $avatarColors['STUDENT'];
                                @endphp
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $color['bg'] }}; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user" style="color: {{ $color['icon'] }};"></i>
                                </div>
                                <span>{{ $user->full_name ?? $user->name }}</span>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @php
                                $roleBadges = [
                                    'STUDENT' => ['class' => 'role-student', 'icon' => 'fa-user-graduate', 'text' => 'Student'],
                                    'INSTRUCTOR' => ['class' => 'role-instructor', 'icon' => 'fa-chalkboard-teacher', 'text' => 'Instructor'],
                                    'ADMIN' => ['class' => 'role-admin', 'icon' => 'fa-user-shield', 'text' => 'Admin'],
                                    'student' => ['class' => 'role-student', 'icon' => 'fa-user-graduate', 'text' => 'Student'],
                                    'instructor' => ['class' => 'role-instructor', 'icon' => 'fa-chalkboard-teacher', 'text' => 'Instructor'],
                                    'admin' => ['class' => 'role-admin', 'icon' => 'fa-user-shield', 'text' => 'Admin'],
                                ];
                                
                                $badge = $roleBadges[$role] ?? $roleBadges['STUDENT'];
                            @endphp
                            <span class="badge {{ $badge['class'] }}">
                                <i class="fas {{ $badge['icon'] }}"></i>
                                {{ $badge['text'] }}
                            </span>
                        </td>
                        <td>
                            <span class="badge status-active">Active</span>
                        </td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="text-right">
                            <div class="table-actions">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="icon-btn" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="icon-btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
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
            @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
                {{ $users->links() }}
            @else
                <!-- Static pagination for now -->
                <button class="pagination-btn disabled">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn">3</button>
                <span class="pagination-ellipsis">...</span>
                <button class="pagination-btn">8</button>
                <button class="pagination-btn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            @endif
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
    
    .role-student {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    .role-instructor {
        background-color: #ede9fe;
        color: #5b21b6;
    }
    
    .role-admin {
        background-color: #fee2e2;
        color: #991b1b;
    }
    
    .status-active {
        background-color: #dcfce7;
        color: #166534;
    }
</style>
@endpush

@push('scripts')
<script>
    // Simple search functionality
    document.getElementById('searchUsers').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
</script>
@endpush