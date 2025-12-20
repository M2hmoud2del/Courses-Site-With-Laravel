// Student Dashboard Logic
let currentView = 'dashboard';
let selectedCourseId = null;

document.addEventListener('DOMContentLoaded', function () {
    // Initialize dashboard with data injected from Blade
    initializeDashboard();
    updateSidebar();
    updateDashboardView();
    populateFilters();
});

function getCurrentUser() {
    return window.user;
}

function getCourses() {
    return window.allCourses || [];
}

function getStudentEnrollments(userId) {
    return window.enrolledCourses || [];
}

function getStudentJoinRequests(userId) {
    return window.joinRequests || [];
}

function getUserNotifications(userId) {
    return window.notifications || [];
}

function getCategories() {
    return window.categories || [];
}

function initializeDashboard() {
    const user = getCurrentUser();

    // Update profile information
    const avatarEls = document.querySelectorAll('#profile-avatar');
    avatarEls.forEach(el => el.textContent = user.name.charAt(0));

    const nameEls = document.querySelectorAll('#profile-name');
    nameEls.forEach(el => el.textContent = user.name);

    const emailEls = document.querySelectorAll('#profile-email');
    emailEls.forEach(el => el.textContent = user.email);

    const welcomeText = document.getElementById('welcome-text');
    if (welcomeText) welcomeText.textContent = `Welcome back, ${user.name}! 👋`;

    // Profile view fields
    const largeAvatar = document.getElementById('profile-avatar-large');
    if (largeAvatar) largeAvatar.textContent = user.name.charAt(0);

    const nameInput = document.getElementById('profile-name-input');
    if (nameInput) nameInput.value = user.name;

    const emailInput = document.getElementById('profile-email-input');
    if (emailInput) emailInput.value = user.email;

    const roleInput = document.getElementById('profile-role-input');
    // if (roleInput) roleInput.value = user.role; // Assuming user.role exists, or hardcode/skip

    // Update notification badge
    const notifications = getUserNotifications(user.id);
    const unreadCount = notifications.filter(n => !n.is_read).length; // Check is_read from Laravel model

    const badgeDot = document.getElementById('notif-badge');
    if (badgeDot) {
        if (unreadCount > 0) {
            badgeDot.style.display = 'block';
        } else {
            badgeDot.style.display = 'none';
        }
    }

    const countBadge = document.getElementById('notif-count-badge');
    if (countBadge) {
        if (unreadCount > 0) {
            countBadge.textContent = unreadCount;
            countBadge.style.display = 'inline-flex';
        } else {
            countBadge.style.display = 'none';
        }
    }
}

function updateSidebar() {
    const user = getCurrentUser();
    const enrolledCourses = getStudentEnrollments(user.id); // These are full course objects in our Laravel implementation

    // Update sidebar courses
    const sidebarCourses = document.getElementById('sidebar-courses');
    if (!sidebarCourses) return;

    if (enrolledCourses.length === 0) {
        sidebarCourses.innerHTML = '<p class="text-center" style="color: var(--gray-500); font-size: 0.875rem; padding: 1rem 0;">No courses enrolled yet</p>';
    } else {
        const progressColors = ['blue', 'purple', 'pink', 'green', 'indigo'];
        sidebarCourses.innerHTML = enrolledCourses.slice(0, 3).map((course, index) => {
            const progress = course.pivot ? course.pivot.progress : 0;
            return `
                <div class="progress-bar">
                    <div class="progress-label">
                        <span style="font-size: 0.875rem; font-weight: 500;">${course.title}</span>
                        <span style="font-size: 0.75rem; color: var(--gray-500);">${progress}%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill ${progressColors[index % progressColors.length]}" style="width: ${progress}%"></div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Update notifications
    const notifications = getUserNotifications(user.id);
    const sidebarNotifications = document.getElementById('sidebar-notifications');
    if (!sidebarNotifications) return;

    if (notifications.length === 0) {
        sidebarNotifications.innerHTML = '<p class="text-center" style="color: var(--gray-500); font-size: 0.875rem; padding: 1rem 0;">No notifications</p>';
    } else {
        sidebarNotifications.innerHTML = notifications.slice(0, 3).map(notif => `
            <div class="notification-item ${notif.is_read ? 'read' : 'unread'}" onclick="markAsRead(${notif.id})">
                <p>${notif.message}</p>
                <p class="notification-time">${new Date(notif.created_at).toLocaleDateString()}</p>
            </div>
        `).join('');
    }
}

function updateDashboardView() {
    const user = getCurrentUser();
    const joinRequests = getStudentJoinRequests(user.id);
    const pendingRequestCourseIds = joinRequests.filter(r => r.status === 'PENDING').map(r => r.course_id);
    const enrolledCourseIds = getStudentEnrollments(user.id).map(c => c.id);

    // Update join requests section
    const joinRequestsSection = document.getElementById('join-requests-section');
    const joinRequestsList = document.getElementById('join-requests-list');

    if (joinRequestsSection && joinRequestsList) {
        if (joinRequests.length > 0) {
            joinRequestsSection.style.display = 'block';
            joinRequestsList.innerHTML = joinRequests.map(request => {
                let statusBadge = '';
                let actionButton = '';

                if (request.status === 'PENDING') {
                    statusBadge = '<span class="badge status-pending"><i class="fas fa-clock"></i> Pending</span>';
                    actionButton = `<button class="btn btn-ghost" style="padding: 0.375rem 0.75rem; font-size: 0.875rem;" onclick="cancelRequest(${request.id})">Cancel</button>`;
                } else if (request.status === 'ACCEPTED') {
                    statusBadge = '<span class="badge status-accepted"><i class="fas fa-check-circle"></i> Accepted</span>';
                } else if (request.status === 'REJECTED') {
                    statusBadge = '<span class="badge status-rejected"><i class="fas fa-times-circle"></i> Rejected</span>';
                }

                // Get course title (we might need to pass it in the joinRequest object or find it)
                // For now assuming joinRequest has course relationship loaded or course_title field
                const courseTitle = request.course ? request.course.title : 'Course #' + request.course_id;
                const requestedDate = new Date(request.created_at).toLocaleDateString();

                return `
                    <div class="request-item">
                        <div class="request-header">
                            <div class="request-info">
                                <h4>${courseTitle}</h4>
                                <p>Requested on ${requestedDate}</p>
                            </div>
                            <div class="request-actions">
                                ${statusBadge}
                                ${actionButton}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            joinRequestsSection.style.display = 'none';
        }
    }

    // Update recommended courses
    // In Blade we pass window.recommendedCourses which are ALREADY filtered and limited.
    const recommendedCourses = window.recommendedCourses || [];

    const recommendedCoursesDiv = document.getElementById('recommended-courses');
    if (recommendedCoursesDiv) {
        recommendedCoursesDiv.innerHTML = recommendedCourses.map(course => {
            const isPending = pendingRequestCourseIds.includes(course.id);
            // Assuming course.instructor is an object from Laravel with 'name' property
            const instructorName = course.instructor ? course.instructor.name : 'Unknown';
            const categoryName = course.category ? course.category.name : 'General';

            return `
                <div class="course-card" onclick="openCourseModal(${course.id})">
                    <div class="course-badges">
                        <span class="badge badge-secondary">${categoryName}</span>
                         <div class="course-rating">
                            <span class="star">★</span>
                            <span style="font-weight: 500;">4.9</span>
                        </div>
                    </div>
                    <h3>${course.title}</h3>
                    <p class="course-instructor">by ${instructorName}</p>
                    <p class="course-description">${course.description}</p>
                    <div class="course-footer">
                        <span class="course-price">$${Number(course.price).toFixed(2)}</span>
                        ${course.is_closed ? '<span class="badge" style="font-size: 0.75rem; border: 1px solid var(--gray-300);">Request to Join</span>' : ''}
                    </div>
                    ${isPending ? '<span class="badge status-pending" style="width: 100%; margin-top: 0.5rem; justify-content: center;">Request Pending</span>' : ''}
                </div>
            `;
        }).join('');
    }
}

function changeView(view) {
    currentView = view;

    // Hide all views
    document.querySelectorAll('.view-content').forEach(v => v.style.display = 'none');

    // Show selected view
    const viewEl = document.getElementById(`view-${view}`);
    if (viewEl) viewEl.style.display = 'block';

    // Update nav buttons
    document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.querySelector(`.nav-btn[data-view="${view}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }

    // Load view data
    if (view === 'courses') {
        updateCoursesView();
    } else if (view === 'enrolled') {
        updateEnrolledView();
    }
}

function populateFilters() {
    const categoryFilter = document.getElementById('category-filter');
    if (categoryFilter) {
        const categories = getCategories();
        categoryFilter.innerHTML = '<option value="all">All Categories</option>' +
            categories.map(cat => `<option value="${cat.name}">${cat.name}</option>`).join('');
    }
}

async function filterCourses() {
    const searchQuery = document.getElementById('search-input').value.toLowerCase();
    const categoryFilter = document.getElementById('category-filter').value;
    const priceFilter = document.getElementById('price-filter').value;

    // We should ideally fetch from server, but if we have window.allCourses loaded, we can filter client side.
    // If window.allCourses is not complete, we should use fetch.
    // The original mockup filtered client side. Let's try to fetch since we have the controller endpoint.

    let url = '/student/browse?';
    if (searchQuery) url += `search=${encodeURIComponent(searchQuery)}&`;
    if (categoryFilter !== 'all') url += `category=${encodeURIComponent(categoryFilter)}&`;
    if (priceFilter !== 'all') url += `price=${encodeURIComponent(priceFilter)}&`;

    try {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const courses = await response.json();
        displayFilteredCourses(courses);
    } catch (error) {
        console.error('Error filtering courses:', error);
    }
}

function updateCoursesView() {
    // Initial load of browse tab could just call filterCourses to get everything
    filterCourses();
}

function displayFilteredCourses(courses) {
    const user = getCurrentUser();
    const joinRequests = getStudentJoinRequests(user.id);
    const pendingRequestCourseIds = joinRequests.filter(r => r.status === 'PENDING').map(r => r.course_id);

    const coursesGrid = document.getElementById('courses-grid');
    const noCoursesMessage = document.getElementById('no-courses-message');

    if (courses.length === 0) {
        coursesGrid.style.display = 'none';
        noCoursesMessage.style.display = 'block';
    } else {
        coursesGrid.style.display = 'grid';
        noCoursesMessage.style.display = 'none';

        coursesGrid.innerHTML = courses.map(course => {
            const isPending = pendingRequestCourseIds.includes(course.id);
            const instructorName = course.instructor ? course.instructor.name : 'Unknown';
            const categoryName = course.category ? course.category.name : 'General';
            const isClosed = course.is_closed; // Laravel field

            return `
                <div class="card" style="cursor: pointer;" onclick="openCourseModal(${course.id})">
                    <div class="course-image"></div>
                    <div class="card-content" style="padding-top: 1rem;">
                        <div class="course-badges">
                            <span class="badge badge-secondary">${categoryName}</span>
                            <div class="course-rating">
                                <span class="star">★</span>
                                <span style="font-weight: 500;">4.9</span>
                            </div>
                        </div>
                        <h3 style="font-size: 1.125rem; margin-bottom: 0.5rem;">${course.title}</h3>
                        <p class="course-instructor">by ${instructorName}</p>
                        <p class="course-description">${course.description}</p>
                        <div class="course-footer">
                            <span class="course-price" style="font-size: 1.25rem;">$${Number(course.price).toFixed(2)}</span>
                            ${isClosed ? '<span class="badge" style="border: 1px solid var(--gray-300);">Closed</span>' : '<span class="badge status-open">Open</span>'}
                        </div>
                        ${isPending ? '<span class="badge status-pending" style="width: 100%; margin-top: 0.75rem; justify-content: center;">Request Pending</span>' : ''}
                    </div>
                </div>
            `;
        }).join('');
    }
}

function updateEnrolledView() {
    const user = getCurrentUser();
    const enrolledCourses = getStudentEnrollments(user.id);

    const enrolledCoursesDiv = document.getElementById('enrolled-courses');
    const noEnrolledMessage = document.getElementById('no-enrolled-message');

    if (enrolledCourses.length === 0) {
        enrolledCoursesDiv.style.display = 'none';
        noEnrolledMessage.style.display = 'flex';
    } else {
        enrolledCoursesDiv.style.display = 'grid';
        noEnrolledMessage.style.display = 'none';

        const progressColors = ['blue', 'purple', 'pink', 'green', 'indigo'];
        enrolledCoursesDiv.innerHTML = enrolledCourses.map((course, index) => {
            const progress = course.pivot ? course.pivot.progress : 0;
            const instructorName = course.instructor ? course.instructor.name : 'Unknown';
            const categoryName = course.category ? course.category.name : 'General';

            return `
                <div class="card">
                     <div class="course-image"></div>
                    <div class="card-content" style="padding-top: 1rem;">
                        <span class="badge badge-secondary" style="margin-bottom: 0.75rem;">${categoryName}</span>
                        <h3 style="font-size: 1.125rem; margin-bottom: 0.5rem;">${course.title}</h3>
                         <p class="course-instructor" style="margin-bottom: 1rem;">by ${instructorName}</p>
                        <div class="progress-bar">
                            <div class="progress-label">
                                <span>Progress</span>
                                <span>${progress}%</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill ${progressColors[index % progressColors.length]}" style="width: ${progress}%"></div>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-full" style="margin-top: 1rem;">Continue Learning</button>
                    </div>
                </div>
            `;
        }).join('');
    }
}

async function openCourseModal(courseId) {
    selectedCourseId = courseId;

    // We might need to fetch full course details if we don't have them
    // But for now let's try to find it in any of the available lists
    let course = (window.recommendedCourses || []).find(c => c.id === courseId) ||
        (window.enrolledCourses || []).find(c => c.id === courseId);

    if (!course) {
        // Fetch it
        try {
            const response = await fetch(`/student/browse?search=${courseId}`); // Hacky way if we don't have a direct endpoint
            // Better: just fetch /student/browse and filter? No, we should have a getCourse endpoint or just rely on passing data.
            // For now, let's assume if it is not in the lists, we can't show it easily without an endpoint.
            // But wait, filterCourses results are not stored in global var.
            // Let's create a temporary store for browsed courses
            course = window.lastBrowsedCourses ? window.lastBrowsedCourses.find(c => c.id === courseId) : null;
        } catch (e) { }
    }

    // Fallback: we might need to fetch single course details.
    // Given the constraints, let's just error if not found, or maybe re-fetch filter?
    // Actually, let's modify filterCourses to store results in window.allCourses for convenience?
    // Or just assign to course if we find it in the DOM (not ideal).

    // Simplest approach: Assume the course object was passed to the click handler context? No, we only passed ID.
    // Let's rely on window.allCourses having everything if possible, or fetch.
    // Since we don't have a "get single course" API, we might be limited.
    // BUT, we can use the `data-course` attribute approach if we rendered it.
    // Let's fetch the list again? No.

    // Let's just modify the renderer to store the data in a map.
    if (!window.courseMap) window.courseMap = {};

    if (window.recommendedCourses) window.recommendedCourses.forEach(c => window.courseMap[c.id] = c);
    if (window.enrolledCourses) window.enrolledCourses.forEach(c => window.courseMap[c.id] = c);

    // If getting from browse
    if (!window.courseMap[courseId]) {
        // Try to fetch via browse API as a workaround
        const response = await fetch('/student/browse');
        const all = await response.json();
        all.forEach(c => window.courseMap[c.id] = c);
    }

    course = window.courseMap[courseId];
    if (!course) return;

    const user = getCurrentUser();
    const joinRequests = getStudentJoinRequests(user.id);
    const isPending = joinRequests.some(r => r.course_id === courseId && r.status === 'PENDING');

    const instructorName = course.instructor ? course.instructor.name : 'Unknown';
    const categoryName = course.category ? course.category.name : 'General';
    const numStudents = course.students_count || 0;

    document.getElementById('modal-course-title').textContent = course.title;
    document.getElementById('modal-course-instructor').textContent = 'by ' + instructorName;
    document.getElementById('modal-course-description').textContent = course.description;
    document.getElementById('modal-course-price').textContent = '$' + Number(course.price).toFixed(2);

    const badgesDiv = document.getElementById('modal-course-badges');
    badgesDiv.innerHTML = `
        <span class="badge badge-secondary">${categoryName}</span>
        <div class="course-rating">
            <span class="star">★</span>
            <span style="font-weight: 500;">4.9</span>
        </div>
        <span class="badge ${!course.is_closed ? 'status-open' : 'status-closed'}">
            ${!course.is_closed ? 'Open Enrollment' : 'Closed - Request Required'}
        </span>
        <span style="font-size: 0.875rem; color: var(--gray-600);">${numStudents} students enrolled</span>
    `;

    const actionBtn = document.getElementById('modal-action-btn');
    const isEnrolled = (window.enrolledCourses || []).some(c => c.id === courseId);

    if (isEnrolled) {
        actionBtn.innerHTML = '<i class="fas fa-check-circle"></i> Enrolled';
        actionBtn.disabled = true;
        actionBtn.classList.remove('btn-primary', 'btn-outline');
        actionBtn.classList.add('btn-ghost');
    } else if (!course.is_closed) {
        actionBtn.innerHTML = '<i class="fas fa-check-circle"></i> Enroll Now';
        actionBtn.onclick = () => {
            showToast('Enrollment feature coming soon!', 'info');
        };
        actionBtn.disabled = false;
        actionBtn.classList.add('btn-primary');
    } else if (isPending) {
        actionBtn.innerHTML = 'Request Pending';
        actionBtn.disabled = true;
        actionBtn.classList.add('btn-outline');
        actionBtn.classList.remove('btn-primary');
    } else {
        actionBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Request to Join';
        actionBtn.disabled = false;
        actionBtn.classList.remove('btn-outline');
        actionBtn.classList.add('btn-primary');
        actionBtn.onclick = () => handleJoinRequest(courseId);
    }

    document.getElementById('course-modal').style.display = 'flex';
}

function closeCourseModal() {
    document.getElementById('course-modal').style.display = 'none';
    selectedCourseId = null;
}

async function handleJoinRequest(courseId) {
    const user = getCurrentUser();

    try {
        const response = await fetch(`/student/join/${courseId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (response.ok) {
            const data = await response.json();
            showToast('Join request sent successfully!', 'success');
            closeCourseModal();

            // Reload page or update data
            // Since we rely on window.* variables, we should reload for simplicity to get fresh data
            window.location.reload();
        } else {
            const err = await response.json();
            showToast(err.message || 'Error sending request', 'error');
        }
    } catch (e) {
        showToast('System error', 'error');
    }
}

async function cancelRequest(requestId) {
    if (confirm('Are you sure you want to cancel this request?')) {
        try {
            const response = await fetch(`/student/requests/${requestId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                showToast('Request cancelled', 'info');
                window.location.reload();
            } else {
                showToast('Error cancelling request', 'error');
            }
        } catch (e) {
            showToast('System error', 'error');
        }
    }
}

async function markAsRead(notificationId) {
    try {
        await fetch(`/student/notifications/${notificationId}/read`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        // We could just update UI locally, but reload is safer for sync
        window.location.reload();
    } catch (e) {
        console.error(e);
    }
}

function toggleNotifications() {
    showToast('Notifications panel coming soon!', 'info');
}

function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast show ${type}`;

    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Helper to intercept and store browsed courses for the modal
const originalDisplayFilteredCourses = displayFilteredCourses;
displayFilteredCourses = function (courses) {
    if (!window.courseMap) window.courseMap = {};
    courses.forEach(c => window.courseMap[c.id] = c);
    originalDisplayFilteredCourses(courses);
}
