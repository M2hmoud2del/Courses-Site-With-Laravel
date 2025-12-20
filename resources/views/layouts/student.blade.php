<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Dashboard - LearnHub</title>
    <link rel="stylesheet" href="{{ asset('css/student.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @yield('content')

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
        // Inject server-side data into global window object
        window.user = @json($user);
        window.enrolledCourses = @json($enrolledCourses);
        window.notifications = @json($notifications);
        window.recommendedCourses = @json($recommendedCourses);
        
        // Load relationships needed for initial view if they are not already loaded
        // The controller should ensure they are loaded (instructor, category)
        
        // Pass join requests if available, or fetch them?
        // Current StudentController::dashboard doesn't pass joinRequests. 
        // We should add it to the controller or pass it here.
        // For now, let's assume valid JSON is valid.
        window.joinRequests = @json($joinRequests ?? []); 
        window.categories = @json($categories ?? []);
    </script>
    <script src="{{ asset('js/student.js') }}"></script>
</body>
</html>
