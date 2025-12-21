@extends('layouts.instructor')

@section('title', $course->title . ' - Students')

@section('instructor-content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>{{ $course->title }}</h2>
            <p>Manage students enrolled in this course</p>
        </div>
        <div class="flex" style="gap: 0.5rem;">
            <a href="{{ route('instructor.courses.show', $course->id) }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Back to Course
            </a>
            <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline">
                <i class="fas fa-list"></i>
                All Courses
            </a>
        </div>
    </div>
</div>

<!-- Course Stats -->
<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card stat-blue">
        <div class="stat-content">
            <p class="stat-label">Total Students</p>
            <div class="stat-value-row">
                <span class="stat-value">{{ $course->enrollments->count() }}</span>
                <i class="fas fa-users stat-icon"></i>
            </div>
            <p class="stat-footer">Currently enrolled</p>
        </div>
    </div>

    <div class="stat-card stat-green">
        <div class="stat-content">
            <p class="stat-label">Average Progress</p>
            <div class="stat-value-row">
                @php
                    $avgProgress = $course->enrollments->avg('progress') ?? 0;
                @endphp
                <span class="stat-value">{{ round($avgProgress, 1) }}%</span>
                <i class="fas fa-chart-line stat-icon"></i>
            </div>
            <p class="stat-footer">Overall course progress</p>
        </div>
    </div>

    <div class="stat-card stat-yellow">
        <div class="stat-content">
            <p class="stat-label">Active Students</p>
            <div class="stat-value-row">
                @php
                    $activeStudents = $course->enrollments->where('progress', '>', 0)->where('progress', '<', 100)->count();
                @endphp
                <span class="stat-value">{{ $activeStudents }}</span>
                <i class="fas fa-user-check stat-icon"></i>
            </div>
            <p class="stat-footer">Currently learning</p>
        </div>
    </div>

    <div class="stat-card stat-purple">
        <div class="stat-content">
            <p class="stat-label">Completed</p>
            <div class="stat-value-row">
                @php
                    $completedStudents = $course->enrollments->where('progress', 100)->count();
                @endphp
                <span class="stat-value">{{ $completedStudents }}</span>
                <i class="fas fa-graduation-cap stat-icon"></i>
            </div>
            <p class="stat-footer">Course finished</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header flex-between">
        <div>
            <h3>Enrolled Students</h3>
            <p>{{ $course->enrollments->count() }} students found</p>
        </div>
        <div class="flex" style="gap: 0.5rem;">
            <div class="input-with-icon">
                <i class="fas fa-search"></i>
                <input type="text" id="searchStudents" placeholder="Search students..." onkeyup="searchTable()">
            </div>
        </div>
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

        @if($course->enrollments->count() > 0)
            <div class="table-responsive">
                <table class="data-table" id="studentsTable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Enrollment Date</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Last Activity</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($course->enrollments as $enrollment)
                        <tr>
                            <td>
                                <div class="student-cell">
                                    <div class="student-avatar">
                                        @if($enrollment->student->profile_picture)
                                            <img src="{{ asset('storage/' . $enrollment->student->profile_picture) }}" alt="{{ $enrollment->student->name }}">
                                        @else
                                            <i class="fas fa-user"></i>
                                        @endif
                                    </div>
                                    <div class="student-info">
                                        <strong>{{ $enrollment->student->name }}</strong>
                                        <div class="student-email">{{ $enrollment->student->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ $enrollment->enrolled_at ? \Carbon\Carbon::parse($enrollment->enrolled_at)->format('M d, Y') : 'N/A' }}
                            </td>
                            <td>
                                <div class="progress-cell">
                                    <div class="progress-label">{{ $enrollment->progress }}%</div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar" style="width: {{ $enrollment->progress }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($enrollment->progress == 0)
                                    <span class="badge status-not-started">
                                        <i class="fas fa-clock"></i>
                                        Not Started
                                    </span>
                                @elseif($enrollment->progress > 0 && $enrollment->progress < 100)
                                    <span class="badge status-in-progress">
                                        <i class="fas fa-spinner"></i>
                                        In Progress
                                    </span>
                                @elseif($enrollment->progress == 100)
                                    <span class="badge status-completed">
                                        <i class="fas fa-check-circle"></i>
                                        Completed
                                    </span>
                                @endif
                            </td>
                            <td>
                                {{ $enrollment->updated_at->diffForHumans() }}
                            </td>
                            <td class="text-right">
                                <div class="table-actions">
                                    <button type="button" class="icon-btn" title="Send Message" onclick="sendMessage({{ $enrollment->student_id }})">
                                        <i class="fas fa-envelope" style="color: var(--blue-500);"></i>
                                    </button>
                                    <button type="button" class="icon-btn" title="View Progress Details" onclick="viewProgress({{ $enrollment->id }})">
                                        <i class="fas fa-chart-bar" style="color: var(--green-500);"></i>
                                    </button>
                                    <form action="{{ route('instructor.enrollments.remove', $enrollment->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove {{ $enrollment->student->name }} from this course?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn" title="Remove from Course">
                                            <i class="fas fa-user-minus" style="color: var(--red-500);"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Progress Summary -->
            <div class="progress-summary" style="margin-top: 2rem; padding: 1.5rem; background: var(--gray-50); border-radius: 0.5rem;">
                <h4 style="margin-bottom: 1rem; font-size: 1rem; font-weight: 600;">Progress Distribution</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    @php
                        $progressRanges = [
                            'Not Started (0%)' => $course->enrollments->where('progress', 0)->count(),
                            'Beginner (1-25%)' => $course->enrollments->where('progress', '>', 0)->where('progress', '<=', 25)->count(),
                            'Intermediate (26-50%)' => $course->enrollments->where('progress', '>', 25)->where('progress', '<=', 50)->count(),
                            'Advanced (51-75%)' => $course->enrollments->where('progress', '>', 50)->where('progress', '<=', 75)->count(),
                            'Near Completion (76-99%)' => $course->enrollments->where('progress', '>', 75)->where('progress', '<', 100)->count(),
                            'Completed (100%)' => $course->enrollments->where('progress', 100)->count(),
                        ];
                    @endphp
                    
                    @foreach($progressRanges as $label => $count)
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span style="font-size: 0.875rem; color: var(--gray-700);">{{ $label }}</span>
                            <span style="font-size: 0.875rem; font-weight: 600; color: var(--gray-900);">{{ $count }}</span>
                        </div>
                        <div style="height: 4px; background: var(--gray-200); border-radius: 2px; overflow: hidden;">
                            <div style="width: {{ $course->enrollments->count() > 0 ? ($count / $course->enrollments->count() * 100) : 0 }}%; height: 100%; background: var(--blue-500);"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-user-graduate" style="font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                <p class="empty-state-text">No students enrolled yet</p>
                <p class="empty-state-subtext">Students will appear here when they enroll in this course</p>
                <div style="margin-top: 1rem; display: flex; gap: 0.5rem; justify-content: center;">
                    <a href="{{ route('instructor.courses.show', $course->id) }}" class="btn btn-outline">
                        <i class="fas fa-info-circle"></i>
                        Course Details
                    </a>
                    <a href="{{ route('instructor.courses.edit', $course->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit Course
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Student Progress Modal -->
<div id="progressModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2>Student Progress Details</h2>
            <p>Detailed progress breakdown</p>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="progressDetails"></div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .student-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray-600);
        overflow: hidden;
    }
    
    .student-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .student-avatar i {
        font-size: 1.25rem;
    }
    
    .student-info {
        flex: 1;
    }
    
    .student-info strong {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-900);
    }
    
    .student-email {
        font-size: 0.75rem;
        color: var(--gray-600);
    }
    
    .progress-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .progress-label {
        min-width: 3rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
    }
    
    .progress-bar-container {
        flex: 1;
        height: 6px;
        background: var(--gray-200);
        border-radius: 3px;
        overflow: hidden;
    }
    
    .progress-bar {
        height: 100%;
        background: var(--green-500);
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    
    /* Badge styles */
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
    
    .badge i {
        font-size: 0.7rem;
    }
    
    .status-not-started {
        background-color: #f3f4f6;
        color: #4b5563;
        border-color: #d1d5db;
    }
    
    .status-in-progress {
        background-color: #dbeafe;
        color: #1e40af;
        border-color: #bfdbfe;
    }
    
    .status-completed {
        background-color: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }
    
    /* Search bar */
    .input-with-icon {
        position: relative;
        width: 250px;
    }
    
    .input-with-icon i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        font-size: 0.875rem;
    }
    
    .input-with-icon input {
        width: 100%;
        padding: 0.5rem 0.75rem 0.5rem 2.25rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
        background: white;
    }
    
    .input-with-icon input:focus {
        outline: none;
        border-color: var(--blue-500);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    /* Modal styles */
    .modal {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }
    
    .modal-content {
        position: relative;
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        max-width: 500px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        z-index: 1001;
    }
    
    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        position: relative;
    }
    
    .modal-header h2 {
        font-size: 1.5rem;
        margin-bottom: 0.25rem;
    }
    
    .modal-header p {
        font-size: 0.875rem;
        color: var(--gray-600);
    }
    
    .modal-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: var(--gray-100);
        border: none;
        border-radius: 0.5rem;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .modal-close:hover {
        background: var(--gray-200);
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    /* Empty state */
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
    
    /* Statistics cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .stat-card {
        border-radius: 0.75rem;
        padding: 1.5rem;
        color: white;
        cursor: pointer;
        transition: transform 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
    }
    
    .stat-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }
    
    .stat-green {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .stat-yellow {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }
    
    .stat-purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }
    
    .stat-content {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .stat-label {
        opacity: 0.9;
        font-size: 0.875rem;
    }
    
    .stat-value-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 600;
    }
    
    .stat-icon {
        font-size: 2rem;
        opacity: 0.8;
    }
    
    .stat-footer {
        opacity: 0.9;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .student-cell {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .progress-cell {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .progress-bar-container {
            width: 100%;
        }
        
        .input-with-icon {
            width: 100%;
        }
        
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .page-header > div {
            width: 100%;
        }
        
        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Search function
    function searchTable() {
        const input = document.getElementById('searchStudents');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('studentsTable');
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            
            for (let j = 0; j < cells.length; j++) {
                if (cells[j]) {
                    const text = cells[j].textContent || cells[j].innerText;
                    if (text.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            
            rows[i].style.display = found ? '' : 'none';
        }
    }
    
    // Modal functions
    function closeModal() {
        document.getElementById('progressModal').style.display = 'none';
    }
    
    function viewProgress(enrollmentId) {
        // In a real application, this would fetch data from the server
        const progressDetails = `
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.125rem; margin-bottom: 0.5rem;">Progress Breakdown</h3>
                <p style="color: var(--gray-600); font-size: 0.875rem;">Detailed view of student's learning journey</p>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                <div style="padding: 1rem; background: var(--gray-50); border-radius: 0.5rem;">
                    <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Overall Progress</h4>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--green-600);">75%</div>
                        <div style="flex: 1; height: 8px; background: var(--gray-200); border-radius: 4px; overflow: hidden;">
                            <div style="width: 75%; height: 100%; background: var(--green-500);"></div>
                        </div>
                    </div>
                </div>
                
                <div style="padding: 1rem; background: var(--gray-50); border-radius: 0.5rem;">
                    <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Activity Timeline</h4>
                    <div style="font-size: 0.875rem; color: var(--gray-600);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span>Last login:</span>
                            <span>2 hours ago</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span>Last assignment:</span>
                            <span>Yesterday</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Total time spent:</span>
                            <span>15 hours</span>
                        </div>
                    </div>
                </div>
                
                <div style="padding: 1rem; background: var(--gray-50); border-radius: 0.5rem;">
                    <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Performance Metrics</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--gray-600);">Assignments</div>
                            <div style="font-size: 1rem; font-weight: 600; color: var(--blue-600);">8/10</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--gray-600);">Average Score</div>
                            <div style="font-size: 1rem; font-weight: 600; color: var(--green-600);">85%</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--gray-600);">Quizzes</div>
                            <div style="font-size: 1rem; font-weight: 600; color: var(--purple-600);">5/6</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--gray-600);">Completion</div>
                            <div style="font-size: 1rem; font-weight: 600; color: var(--yellow-600);">75%</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 1.5rem; text-align: center;">
                <button class="btn btn-primary" onclick="closeModal()">
                    <i class="fas fa-check"></i>
                    Close
                </button>
            </div>
        `;
        
        document.getElementById('progressDetails').innerHTML = progressDetails;
        document.getElementById('progressModal').style.display = 'flex';
    }
    
    function sendMessage(studentId) {
        alert(`Message feature for student ID: ${studentId} would open here.`);
        // In a real application, this would open a messaging interface
    }
</script>
@endpush