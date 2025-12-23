# 📚 Courses Site Platform - Full Project Explanation

This is a **Laravel-based educational platform** that allows **Instructors** to create and manage courses, **Students** to browse and enroll, and **Admins** to manage the entire system.

---

## 🏗️ Project Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     USER ROLES (RBAC)                       │
├──────────────┬──────────────────┬──────────────────────────┤
│   STUDENT    │   INSTRUCTOR     │         ADMIN            │
└──────────────┴──────────────────┴──────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    COURSE SYSTEM                            │
│  ┌─────────┐    ┌─────────────┐    ┌──────────────────┐    │
│  │ Courses │ ◄──│ Enrollments │    │  Join Requests   │    │
│  └─────────┘    └─────────────┘    │  (for closed)    │    │
│       │                             └──────────────────┘    │
│       ▼                                                     │
│  ┌───────────────────┐                                      │
│  │  CourseContent    │                                      │
│  └───────────────────┘                                      │
└─────────────────────────────────────────────────────────────┘
```

---

## 👨‍🎓 STUDENT PART - Detailed Explanation

### 1. Student Model & Database

The `User` model in `app/Models/User.php` serves all roles. A student is identified by `role = 'STUDENT'`.

**Key Relationships for Students:**

| Relationship | Method | Description |
|--------------|--------|-------------|
| **Enrolled Courses** | `courses()` | Many-to-Many via `enrollments` table |
| **Join Requests** | `joinRequests()` | One-to-Many for pending course requests |
| **Notifications** | `notifications()` | One-to-Many for system messages |

```php
// User.php - Student relationships
public function courses() {
    return $this->belongsToMany(Course::class, 'enrollments', 'student_id', 'course_id')
        ->withPivot('progress', 'enrolled_at')
        ->withTimestamps();
}

public function joinRequests() {
    return $this->hasMany(JoinRequest::class, 'student_id');
}
```

---

### 2. Student Controller

**File:** `app/Http/Controllers/StudentController.php`

This is the **main controller** handling all student actions:

| Method | Route | Description |
|--------|-------|-------------|
| `dashboard()` | `/dashboard` | Main student dashboard |
| `browse()` | `/student/browse` | Browse available courses with filters |
| `enrolled()` | `/student/enrolled` | Get enrolled courses |
| `enroll()` | `POST /student/enroll/{course}` | Direct enrollment in **open** courses |
| `requestJoin()` | `POST /student/join/{course}` | Request to join a **closed** course |
| `cancelJoinRequest()` | `DELETE /student/requests/{request}` | Cancel pending request |
| `showCourse()` | `/student/courses/{course}` | View course details |
| `updateProfile()` | `POST /student/profile` | Update name & avatar |
| `updatePassword()` | `PUT /student/password` | Change password |
| `notifications()` | `/student/notifications` | Get all notifications |
| `markNotificationAsRead()` | `PATCH /student/notifications/{id}/read` | Mark as read |

---

### 3. Student Enrollment Flow

There are **two types of courses**:

#### A. Open Courses (`is_closed = false`)

```
Student ──► Browse Courses ──► Click "Enroll" ──► Instant Enrollment ──► Added to Enrollments Table
```

**Code Logic (`enroll()`):**

```php
if ($course->is_closed) {
    return response()->json(['message' => 'Course is closed for direct enrollment'], 400);
}
$user->courses()->attach($courseId); // Direct enrollment
```

#### B. Closed/Restricted Courses (`is_closed = true`)

```
Student ──► Browse Courses ──► Click "Request to Join" ──► JoinRequest Created (PENDING)
                                                              │
                                                              ▼
Instructor approves/rejects ──► If approved: Enrollment created, JoinRequest deleted
```

**Code Logic (`requestJoin()`):**

```php
$joinRequest = JoinRequest::create([
    'student_id' => $user->id,
    'course_id' => $course->id,
    'status' => 'PENDING',
]);
```

---

### 4. Student Dashboard Features

The dashboard in `resources/views/dashboard.blade.php` provides:

```
┌──────────────────────────────────────────────────────────────────┐
│  STUDENT DASHBOARD                                               │
├────────────────────┬─────────────────────────────────────────────┤
│  SIDEBAR           │  MAIN CONTENT                               │
├────────────────────┼─────────────────────────────────────────────┤
│  • Profile Card    │  Views:                                     │
│  • My Courses      │  ┌─────────────────────────────────────────┐│
│    (Quick list)    │  │ 1. Dashboard View                       ││
│  • Notifications   │  │    - Welcome banner                     ││
│                    │  │    - Join requests status               ││
│                    │  │    - Recommended courses                ││
│                    │  ├─────────────────────────────────────────┤│
│                    │  │ 2. Browse Courses View                  ││
│                    │  │    - Search bar                         ││
│                    │  │    - Category filter                    ││
│                    │  │    - Price filter (Free/Paid)           ││
│                    │  │    - Course grid                        ││
│                    │  ├─────────────────────────────────────────┤│
│                    │  │ 3. My Courses (Enrolled)                ││
│                    │  │    - All enrolled courses               ││
│                    │  │    - Progress tracking                  ││
│                    │  ├─────────────────────────────────────────┤│
│                    │  │ 4. Profile View                         ││
│                    │  │    - Update name & avatar               ││
│                    │  │    - Change password                    ││
│                    │  └─────────────────────────────────────────┘│
└────────────────────┴─────────────────────────────────────────────┘
```

---

### 5. Student Routes

**File:** `routes/web.php`

```php
Route::middleware(['auth', 'verified', 'role:STUDENT'])->prefix('student')->name('student.')->group(function () {
    Route::get('/browse', [StudentController::class, 'browse']);           // API: Get courses
    Route::get('/enrolled', [StudentController::class, 'enrolled']);       // API: Get enrolled courses
    Route::get('/courses/{course}', [StudentController::class, 'showCourse']); // View course detail
    Route::post('/join/{course}', [StudentController::class, 'requestJoin']); // Request to join
    Route::delete('/requests/{request}', [StudentController::class, 'cancelJoinRequest']);
    Route::post('/enroll/{course}', [StudentController::class, 'enroll']); // Direct enroll
    Route::post('/profile', [StudentController::class, 'updateProfile']);
    Route::put('/password', [StudentController::class, 'updatePassword']);
});
```

---

### 6. Database Tables for Students

| Table | Key Fields | Purpose |
|-------|------------|---------|
| `users` | `id, name, email, role='STUDENT', profile_picture` | Student account |
| `enrollments` | `student_id, course_id, progress, enrolled_at` | Tracks enrolled courses |
| `join_requests` | `student_id, course_id, status, request_date` | Pending requests for closed courses |
| `notifications` | `recipient_id, message, is_read, date` | System notifications |

---

### 7. Course Browsing & Filtering

The `browse()` method supports:

```php
// Search by keyword
$query->where('title', 'like', "%{$search}%")
      ->orWhere('description', 'like', "%{$search}%");

// Filter by category
$query->whereHas('category', fn($q) => $q->where('name', $request->category));

// Filter by price
if ($request->price === 'free') $query->where('price', 0);
if ($request->price === 'paid') $query->where('price', '>', 0);

// Exclude already enrolled courses
$query->whereNotIn('id', $enrolledIds);
```

---

## 📊 Class Diagram Summary (Student Focused)

```
         ┌──────────────┐
         │    User      │ (Abstract base)
         │  role=STUDENT│
         └──────┬───────┘
                │
      ┌─────────┼─────────────┐
      │         │             │
      ▼         ▼             ▼
┌──────────┐ ┌──────────┐ ┌──────────────┐
│Enrollment│ │JoinRequest│ │ Notification │
└────┬─────┘ └─────┬─────┘ └──────────────┘
     │             │
     ▼             ▼
┌──────────────────────┐
│       Course         │
│  - title             │
│  - description       │
│  - price             │
│  - is_closed         │
│  - instructor_id     │
│  - category_id       │
└──────────────────────┘
```

---

## 🔐 Access Control

All student routes are protected with middleware:

- `auth` - Must be logged in
- `verified` - Email must be verified
- `role:STUDENT` - Must have student role

---

## 🎯 Key Student Use Cases

1. **Browse & Search Courses** → Filter by category, price, keyword
2. **Enroll in Open Courses** → Immediate access
3. **Request to Join Closed Courses** → Wait for instructor approval
4. **Track Enrolled Courses** → View progress and course content
5. **Manage Profile** → Update name, avatar, password
6. **Receive Notifications** → System alerts (enrollment approved, etc.)

---

## 📁 Key Files for Student Functionality

| File | Purpose |
|------|---------|
| `app/Models/User.php` | User model with student relationships |
| `app/Models/Enrollment.php` | Enrollment model (student-course link) |
| `app/Models/JoinRequest.php` | Join request model for closed courses |
| `app/Models/Notification.php` | Notification model |
| `app/Http/Controllers/StudentController.php` | All student actions |
| `resources/views/dashboard.blade.php` | Student dashboard view |
| `resources/views/layouts/student.blade.php` | Student layout template |
| `resources/css/student.css` | Student dashboard styles |
| `resources/js/student.js` | Student dashboard JavaScript |
| `routes/web.php` | Route definitions |

---

## 🔄 Student Workflow Summary

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        STUDENT WORKFLOW                                  │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────┐     ┌──────────┐     ┌─────────────────────────────────┐  │
│  │ Register│ ──► │  Login   │ ──► │        Dashboard                │  │
│  └─────────┘     └──────────┘     └─────────────┬───────────────────┘  │
│                                                  │                       │
│                      ┌───────────────────────────┼───────────────────┐  │
│                      ▼                           ▼                   ▼  │
│              ┌─────────────┐           ┌─────────────┐      ┌────────┐ │
│              │   Browse    │           │  My Courses │      │Profile │ │
│              │   Courses   │           │  (Enrolled) │      │Settings│ │
│              └──────┬──────┘           └─────────────┘      └────────┘ │
│                     │                                                   │
│         ┌───────────┴───────────┐                                      │
│         ▼                       ▼                                      │
│  ┌─────────────┐        ┌─────────────┐                                │
│  │ Open Course │        │Closed Course│                                │
│  │   Enroll    │        │Request Join │                                │
│  │  Directly   │        │   (Pending) │                                │
│  └──────┬──────┘        └──────┬──────┘                                │
│         │                      │                                        │
│         │                      ▼                                        │
│         │              ┌─────────────────┐                             │
│         │              │Instructor Review│                             │
│         │              │Approve / Reject │                             │
│         │              └────────┬────────┘                             │
│         │                       │                                       │
│         └───────────────────────┴──────────────────┐                   │
│                                                    ▼                    │
│                                            ┌─────────────┐             │
│                                            │  Enrolled!  │             │
│                                            │ View Course │             │
│                                            │   Content   │             │
│                                            └─────────────┘             │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

*Documentation generated for Courses Site Platform*
