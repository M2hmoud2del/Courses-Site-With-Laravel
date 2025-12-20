@extends('layouts.instructor')

@section('title', 'Join Requests')

@section('content')

<div class="page-header">
    <h1>Join Requests</h1>
    <p>Approve or reject students</p>
</div>

<div class="card">
    <div class="card-content table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="requests-table"></tbody>
        </table>

        <div id="no-requests-message" class="empty-state" style="display:none">
            <p>No join requests</p>
        </div>
    </div>
</div>

@endsection
