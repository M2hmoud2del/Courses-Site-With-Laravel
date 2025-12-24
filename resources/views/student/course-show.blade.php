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
                                    <span style="font-weight: 600;" id="progress-text">{{ $progress }}%</span>
                                </div>
                                <div class="progress-track">
                                    <div class="progress-fill blue" id="progress-fill" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                                <p style="font-size: 0.875rem; color: var(--gray-500); margin: 0;">
                                    <i class="fas fa-calendar"></i>
                                    Enrolled on {{ $enrolledAt ? \Carbon\Carbon::parse($enrolledAt)->format('M d, Y') : 'Unknown' }}
                                </p>
                                @if(isset($currentContent))
                                <button onclick="scrollToContent()" class="btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    <i class="fas fa-play"></i> Continue Learning
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Player Section -->
            @if(isset($currentContent))
            <div id="content-player" class="card" style="margin-top: 2rem; display: none;">
                <div class="card-header" style="justify-content: space-between; align-items: center;">
                    <h3><i class="{{ $currentContent->type_icon }}"></i> {{ $currentContent->title }}</h3>
                    <span class="badge badge-primary">{{ $currentContent->type_label }}</span>
                </div>
                <div class="card-content">
                    <div class="content-viewer">
                        @if($currentContent->content_type === 'VIDEO')
                            <div class="video-container">
                                <iframe src="{{ $currentContent->embed_url }}" frameborder="0" allowfullscreen></iframe>
                            </div>
                        @elseif($currentContent->content_type === 'LINK')
                            <div class="link-container" style="text-align: center; padding: 3rem; background: var(--gray-50); border-radius: 0.5rem;">
                                <i class="fas fa-link" style="font-size: 3rem; color: var(--blue-500); margin-bottom: 1rem;"></i>
                                <p>This content is an external link:</p>
                                <a href="{{ $currentContent->external_link }}" target="_blank" class="btn-primary">
                                    Open {{ $currentContent->platform ?? 'Link' }} <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        @else
                            <div class="text-content" style="padding: 1rem; line-height: 1.6;">
                                {!! nl2br(e($currentContent->content)) !!}
                            </div>
                        @endif
                    </div>

                    <div class="content-actions" style="margin-top: 2rem; display: flex; justify-content: flex-end; border-top: 1px solid var(--gray-200); padding-top: 1rem;">
                        @php
                            $isLastContent = $course->contents->last()->id === $currentContent->id;
                            $isCompleted = in_array($currentContent->id, $completedContentIds ?? []);
                        @endphp
                        <button id="mark-complete-btn" class="btn-primary" onclick="markComplete({{ $course->id }}, {{ $currentContent->id }})">
                            @if($isLastContent)
                                @if($isCompleted)
                                    <i class="fas fa-check-double"></i> Course Completed
                                @else
                                    <i class="fas fa-flag-checkered"></i> Finish Course
                                @endif
                            @else
                                <i class="fas fa-arrow-right"></i> Next Lesson
                            @endif
                        </button>
                    </div>
                </div>
            </div>
            @endif

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

        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 */
            height: 0;
            overflow: hidden;
            border-radius: 0.5rem;
            background: #000;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
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

        /* Animations */
        @keyframes slideOutLeft {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(-50px); opacity: 0; }
        }
        
        @keyframes slideInRight {
            from { transform: translateX(50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .slide-out {
            animation: slideOutLeft 0.3s ease forwards;
        }
        
        .slide-in {
            animation: slideInRight 0.3s ease forwards;
        }
    </style>

    <script>
        function scrollToContent() {
            const player = document.getElementById('content-player');
            if (player) {
                player.style.display = 'block';
                player.scrollIntoView({ behavior: 'smooth' });
            }
        }

        function markComplete(courseId, contentId) {
            const btn = document.getElementById('mark-complete-btn');
            const originalText = btn.innerHTML;
            const playerCard = document.getElementById('content-player');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            fetch(`/student/courses/${courseId}/content/${contentId}/complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    // Update progress bar
                    document.getElementById('progress-text').innerText = data.progress + '%';
                    document.getElementById('progress-fill').style.width = data.progress + '%';

                    if (data.next_content) {
                        // Slide out current content
                        playerCard.classList.add('slide-out');
                        
                        setTimeout(() => {
                            // Update content
                            const headerTitle = playerCard.querySelector('.card-header h3');
                            const badge = playerCard.querySelector('.badge');
                            const contentViewer = playerCard.querySelector('.content-viewer');
                            
                            // Update Header
                            headerTitle.innerHTML = `<i class="${data.next_content.type_icon}"></i> ${data.next_content.title}`;
                            badge.textContent = data.next_content.type_label;
                            
                            // Update Viewer
                            let viewerHtml = '';
                            if (data.next_content.content_type === 'VIDEO') {
                                viewerHtml = `
                                    <div class="video-container">
                                        <iframe src="${data.next_content.embed_url}" frameborder="0" allowfullscreen></iframe>
                                    </div>`;
                            } else if (data.next_content.content_type === 'LINK') {
                                viewerHtml = `
                                    <div class="link-container" style="text-align: center; padding: 3rem; background: var(--gray-50); border-radius: 0.5rem;">
                                        <i class="fas fa-link" style="font-size: 3rem; color: var(--blue-500); margin-bottom: 1rem;"></i>
                                        <p>This content is an external link:</p>
                                        <a href="${data.next_content.external_link}" target="_blank" class="btn-primary">
                                            Open ${data.next_content.platform || 'Link'} <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>`;
                            } else {
                                viewerHtml = `
                                    <div class="text-content" style="padding: 1rem; line-height: 1.6;">
                                        ${data.next_content.content}
                                    </div>`;
                            }
                            contentViewer.innerHTML = viewerHtml;

                            // Update Button
                            btn.innerHTML = '<i class="fas fa-arrow-right"></i> Next Lesson';
                            btn.onclick = function() { markComplete(courseId, data.next_content.id); };
                            btn.disabled = false;
                            
                            // Remove slide-out and add slide-in
                            playerCard.classList.remove('slide-out');
                            playerCard.classList.add('slide-in');
                            
                            // Clean up slide-in class after animation
                            setTimeout(() => {
                                playerCard.classList.remove('slide-in');
                            }, 300);
                            
                        }, 300); // Wait for slide-out animation
                    } else if (data.course_completed) {
                        btn.innerHTML = '<i class="fas fa-check-double"></i> Course Completed';
                        btn.classList.remove('btn-primary');
                        btn.style.backgroundColor = 'var(--green-500)';
                        btn.style.color = 'white';
                        // Optional: Show confetti or redirect
                    } else {
                         btn.innerHTML = '<i class="fas fa-check-circle"></i> Completed';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.disabled = false;
                btn.innerHTML = originalText;
                alert('An error occurred. Please try again.');
            });
        }
    </script>
@endsection
