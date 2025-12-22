# Project Documentation: Courses Site Platform

## 1. Project Description
The **Courses Site Platform** is a web-based educational management system built with the **Laravel** framework. It facilitates the interaction between three primary user roles: **Students**, **Instructors**, and **Admins**. The platform allows Instructors to publish and manage courses, Students to browse, enroll, and track their progress, and Admins to oversee the entire ecosystem, including user and content management.

The system features a robust authentication system with role-based access control (RBAC), ensuring secure and appropriate access to features. Key functionalities include course catalog browsing with filters, student enrollment workflows (direct or request-based), dashboard analytics for instructors, and profile management.

---

## 2. Requirements Analysis

### 2.1 Functional Requirements
These requirements define the specific behaviors and functions the system supports.

| ID | Category | Requirement Description | Actor(s) |
| :--- | :--- | :--- | :--- |
| **FR-01** | **Authentication** | Users must be able to register, login, and logout securely. | All |
| **FR-02** | **Profile Mgmt** | Users can update their profile information (name, avatar) and change passwords. | All |
| **FR-03** | **Course Mgmt** | Instructors can create, edit, update, and delete their courses. | Instructor |
| **FR-04** | **Content Mgmt** | Instructors can add chapters and lessons (content) to their courses. | Instructor |
| **FR-05** | **Browsing** | Students can browse courses and filter by category, price, or search by keyword. | Student |
| **FR-06** | **Enrollment** | Students can enroll in open courses immediately or request to join restricted courses. | Student |
| **FR-07** | **Approvals** | Instructors can approve or reject course join requests from students. | Instructor |
| **FR-08** | **Dashboard** | Students have a dashboard showing enrolled courses, notifications, and recommendations. | Student |
| **FR-09** | **User Admin** | Admins can view, create, edit, and delete user accounts. | Admin |
| **FR-10** | **Notifications** | Users receive notifications for important events (e.g., enrollment approval). | Student |

### 2.2 Non-Functional Requirements
These requirements define system attributes such as security, reliability, and performance.

| ID | Type | Requirement Description |
| :--- | :--- | :--- |
| **NFR-01** | **Security** | Passwords must be hashed. Access to routes must be protected by Role-Based Access Control (RBAC). |
| **NFR-02** | **Performance** | Pages should load within 2 seconds under normal load. Database queries should be optimized. |
| **NFR-03** | **Usability** | The interface should be responsive and accessible on both desktop and mobile devices. |
| **NFR-04** | **Reliability** | The system should handle input validation errors gracefully and provide user-friendly feedback. |
| **NFR-05** | **Scalability** | The database schema should support future expansion of course types and user roles. |

---

## 3. System Modeling

### 3.1 Use Case Diagram
The following diagram illustrates the interaction between actors and the system's major use cases.

```mermaid
usecaseDiagram
    actor Student
    actor Instructor
    actor Admin

    package "Course Platform System" {
        usecase "Login / Register" as UC1
        usecase "Manage Profile" as UC2
        
        usecase "Browse Courses" as UC3
        usecase "Enroll in Course" as UC4
        usecase "View Progress" as UC5
        
        usecase "Manage Courses (CRUD)" as UC6
        usecase "Manage Course Content" as UC7
        usecase "Approve Join Requests" as UC8
        usecase "View Enrollments" as UC9
        
        usecase "Manage Users" as UC10
        usecase "Manage Categories" as UC11
    }

    Student --> UC1
    Student --> UC2
    Student --> UC3
    Student --> UC4
    Student --> UC5

    Instructor --> UC1
    Instructor --> UC2
    Instructor --> UC6
    Instructor --> UC7
    Instructor --> UC8
    Instructor --> UC9

    Admin --> UC1
    Admin --> UC2
    Admin --> UC10
    Admin --> UC11
```

### 3.2 Class Diagram
This diagram represents the static structure of the system's primary models and their relationships.

```mermaid
classDiagram
    class User {
        +Integer id
        +String name
        +String email
        +String role
        +isStudent()
        +isInstructor()
        +isAdmin()
    }

    class Course {
        +Integer id
        +String title
        +Decimal price
        +Boolean is_closed
        +students()
        +instructor()
        +contents()
    }

    class Enrollment {
        +Integer id
        +DateTime enrolled_at
        +Decimal progress
    }

    class Category {
        +Integer id
        +String name
    }

    class JoinRequest {
        +Integer id
        +String status
        +DateTime request_date
    }

    class CourseContent {
        +Integer id
        +String title
        +String type
        +Integer order
    }

    User "1" --> "*" Course : teaches (Instructor)
    User "*" --> "*" Course : enrolls (Student)
    (User, Course) .. Enrollment
    Course "1" --> "*" JoinRequest
    User "1" --> "*" JoinRequest
    Category "1" --> "*" Course
    Course "1" --> "*" CourseContent
```

---

## 4. Test Cases & Verification

This section outlines selected test cases covering both functional (Black Box) and structural (White Box) aspects of the system.

### 4.1 Black Box Testing (Functional)
Focuses on input/output without knowledge of internal code structure.

| Test Case ID | Description | Pre-conditions | Test Steps | Expected Result |
| :--- | :--- | :--- | :--- | :--- |
| **BB-01** | **Direct Enrollment** | Student logged in, Course is "Open" | 1. Navigate to Course page<br>2. Click "Enroll"<br>3. Confirm action | Success message displayed; Course appears in "My Courses". |
| **BB-02** | **Restricted Enrollment** | Student logged in, Course is "Closed/Private" | 1. Navigate to Course page<br>2. Click "Request to Join" | Success message displayed; Request status becomes "Pending". |
| **BB-03** | **Search Functionality** | Student on "Browse" page | 1. Enter keyword "Laravel"<br>2. Click Search | List updates to show only courses containing "Laravel" in title. |
| **BB-04** | **Instructor Course Creation** | Instructor logged in | 1. Click "Create Course"<br>2. Fill details (Title, Price)<br>3. Submit | Course is created and visible in Instructor dashboard. |
| **BB-05** | **Permission Denied** | Student logged in | 1. Attempt to access `/admin/dashboard` URL directly | System redirects to home or shows 403 Forbidden error. |

### 4.2 White Box Testing (Structural)
Focuses on internal logic, validation paths, and code coverage. (References `tests/Feature/StudentControllerTest.php`)

| Test Case ID | Description | Component / Method | Test Logic / Path | Expected Result |
| :--- | :--- | :--- | :--- | :--- |
| **WB-01** | **Validation: Profile Update** | `StudentController@updateProfile` | **Input**: Empty name string.<br>**Path**: `validate()` fails.<br>**Check**: Validation error returned. | HTTP 422 Unprocessable Entity; Error key: `name`. |
| **WB-02** | **Logic: Duplicate Enrollment** | `StudentController@requestJoin` | **Input**: Course ID where user is already enrolled.<br>**Path**: Check `enrollments` table -> If exists -> Abort.<br>**Check**: Controller returns error. | HTTP 400 Bad Request; Message: "Already enrolled". |
| **WB-03** | **Logic: Password Change** | `StudentController@updatePassword` | **Input**: Incorrect `current_password`.<br>**Path**: `Hash::check` fails.<br>**Check**: Validation rule halts execution. | HTTP 422 Unprocessable Entity; Error key: `current_password`. |
| **WB-04** | **Integration: Enrollment Flow** | `StudentController@enroll` | **Action**: Successful enrollment.<br>**Side Effect**: Check if pending `JoinRequest` is deleted (cleanup logic). | Database: New `enrollment` row exists; `join_request` row is deleted. |
| **WB-05** | **Middleware: Role Access** | `Middleware\Role` | **Action**: User with role 'STUDENT' accesses Protected Route.<br>**Logic**: `Auth::user()->role === 'STUDENT'` check. | Access Granted (HTTP 200). (Contrast with BB-05). |

---

*Documentation generated by Antigravity AI.*
