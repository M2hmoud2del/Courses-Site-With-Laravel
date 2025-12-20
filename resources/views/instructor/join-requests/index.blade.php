@extends('layouts.instructor')

@section('title', 'Join Requests')

@section('instructor-content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Join Requests</h2>
            <p>Manage student requests to join your courses</p>
        </div>
        <div class="flex" style="gap: 0.5rem;">
            <a href="{{ route('instructor.dashboard') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Pending Requests</h3>
        <p>Total: {{ $joinRequests->total() }} requests</p>
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

        @if($joinRequests->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($joinRequests as $request)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--gray-200); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user" style="color: var(--gray-600);"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $request->student->name }}</strong>
                                        <div style="font-size: 0.75rem; color: var(--gray-600);">
                                            {{ $request->student->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-weight: 500;">{{ $request->course->title }}</td>
                            <td>{{ $request->request_date->format('M d, Y H:i') }}</td>
                            <td>
                                <span class="badge status-pending">
                                    <i class="fas fa-clock"></i>
                                    Pending
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="table-actions">
                                    <form action="{{ route('instructor.join-requests.approve', $request->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="icon-btn" title="Approve" onclick="return confirm('Approve this join request?')">
                                            <i class="fas fa-check" style="color: #10b981;"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('instructor.join-requests.reject', $request->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="icon-btn" title="Reject" onclick="return confirm('Reject this join request?')">
                                            <i class="fas fa-times" style="color: #ef4444;"></i>
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
                {{ $joinRequests->links('vendor.pagination.custom') }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-inbox" style="font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                <p>No pending join requests</p>
                <p style="color: var(--gray-500); font-size: 0.875rem;">All join requests have been processed</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
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
    
    .empty-state p {
        margin: 0.5rem 0;
    }
</style>
@endpush