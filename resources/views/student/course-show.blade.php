@extends('layouts.student')

@section('content')
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-container">
            <div class="nav-left">
                <div class="logo" onclick="window.location.href='{{ route('dashboard') }}'">
                    <i class="fas fa-book-open"></i>
                    <span>LearnHub</span>
                </div>
                <div class="nav-links">
                    <button class="nav-btn" onclick="window.location.href='{{ route('dashboard') }}'">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Dashboard</span>
                    </button>
                </div>
            </div>
            <div class="nav-right">
                <!-- Logout Form -->
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-ghost" style="border: none; cursor: pointer;">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <div class="course-detail-container">
            <!-- Course Header -->
            <div class="card">
                <div class="course-header-section">
                    <div class="course-image-large"></div>
                    <div class="course-header-content">
                        <div class="course-badges">
                            <span class="badge badge-secondary">{{ $course->category ? $course->category->name : 'General' }}</span>
                            <div class="course-rating">
                                <span class="star">★</span>
                                <span style="font-weight: 500;">4.9</span>
                            </div>
                        </div>
                        <h1 style="font-size: 2rem; margin: 1rem 0;">{{ $course->title }}</h1>
                        <p class="course-instructor" style="font-size: 1.125rem;">
                            <i class="fas fa-user-tie"></i>
                            by {{ $course->instructor ? $course->instructor->name : 'Unknown Instructor' }}
                        </p>
                        
                        <!-- Progress Bar -->
                        <div class="progress-section" style="margin-top: 1.5rem;">
                            <div class="progress-bar">
                                <div class="progress-label">
                                    <span style="font-weight: 500;">Your Progress</span>
                                    <span style="font-weight: 600;">{{ $progress }}%</span>
                                </div>
                                <div class="progress-track">
                                    <div class="progress-fill blue" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                            <p style="font-size: 0.875rem; color: var(--gray-500); margin-top: 0.5rem;">
                                <i class="fas fa-calendar"></i>
                                Enrolled on {{ $enrolledAt ? \Carbon\Carbon::parse($enrolledAt)->format('M d, Y') : 'Unknown' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Details Grid -->
            <div class="course-details-grid">
                <!-- Course Description -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> Course Overview</h3>
                    </div>
                    <div class="card-content">
                        <p style="font-size: 1rem; line-height: 1.6; color: var(--gray-700);">
                            {{ $course->description ?: 'No description available for this course.' }}
                        </p>
                    </div>
                </div>

                <!-- Course Information -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-graduation-cap"></i> Course Information</h3>
                    </div>
                    <div class="card-content">
                        <div class="info-grid">
                            <div class="info-item">
                                <i class="fas fa-user-tie" style="color: var(--blue-600);"></i>
                                <div>
                                    <p class="info-label">Instructor</p>
                                    <p class="info-value">{{ $course->instructor ? $course->instructor->name : 'Unknown' }}</p>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-tag" style="color: var(--purple-600);"></i>
                                <div>
                                    <p class="info-label">Category</p>
                                    <p class="info-value">{{ $course->category ? $course->category->name : 'General' }}</p>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-dollar-sign" style="color: var(--green-600);"></i>
                                <div>
                                    <p class="info-label">Price</p>
                                    <p class="info-value">${{ number_format($course->price, 2) }}</p>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-lock-open" style="color: var(--indigo-600);"></i>
                                <div>
                                    <p class="info-label">Enrollment</p>
                                    <p class="info-value">{{ $course->is_closed ? 'Closed' : 'Open' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .course-detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .course-header-section {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            align-items: start;
        }

        .course-image-large {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, var(--blue-500), var(--purple-500));
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .course-header-content {
            padding: 1rem 0;
        }

        .progress-section {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid var(--gray-200);
        }

        .course-details-grid {
            margin-top: 2rem;
            display: grid;
            gap: 2rem;
        }

        .content-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .content-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: 0.75rem;
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }

        .content-item:hover {
            border-color: var(--blue-400);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }

        .content-item-header {
            display: flex;
            gap: 1rem;
            align-items: start;
            flex: 1;
        }

        .content-number {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--blue-100);
            color: var(--blue-600);
            border-radius: 50%;
            font-weight: 600;
            flex-shrink: 0;
        }

        .content-info h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0 0 0.25rem 0;
        }

        .content-info p {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin: 0;
        }

        .content-type-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
            white-space: nowrap;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            gap: 1rem;
            align-items: start;
        }

        .info-item i {
            font-size: 1.5rem;
            margin-top: 0.25rem;
        }

        .info-label {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin: 0 0 0.25rem 0;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0;
        }

        @media (max-width: 768px) {
            .course-header-section {
                grid-template-columns: 1fr;
            }

            .course-image-large {
                height: 200px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
