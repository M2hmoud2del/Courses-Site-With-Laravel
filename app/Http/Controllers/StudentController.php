<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\JoinRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        // Recent enrolled courses
        $enrolledCourses = $user->courses()
            ->with(['instructor', 'category'])
            ->orderByPivot('enrolled_at', 'desc')
            ->take(3)
            ->get();
            
        // Recent notifications
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Recommended courses (excluding enrolled ones)
        $recommendedCourses = Course::whereDoesntHave('students', function($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->where('is_closed', false)
            ->inRandomOrder()
            ->take(4)
            ->with(['instructor', 'category'])
            ->get();

        // Join requests
        $joinRequests = $user->joinRequests()
            ->with(['course'])
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = Category::all();

        return view('dashboard', compact('user', 'enrolledCourses', 'notifications', 'recommendedCourses', 'joinRequests', 'categories'));
    }

    public function browse(Request $request)
    {
        $query = Course::query()
            ->where('is_closed', false)
            ->with(['instructor:id,name', 'category:id,name']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by Category
        if ($request->has('category') && $request->category !== 'all') {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // Filter by Price
        if ($request->has('price')) {
            if ($request->price === 'free') {
                $query->where('price', 0);
            } elseif ($request->price === 'paid') {
                $query->where('price', '>', 0);
            }
        }

        // Exclude enrolled courses
        $user = $request->user();
        if ($user) {
            $enrolledIds = $user->courses()->pluck('courses.id');
            $query->whereNotIn('id', $enrolledIds);
        }

        return response()->json($query->get());
    }

    public function enrolled(Request $request)
    {
        $user = $request->user();
        $enrolled = $user->courses()
            ->with(['instructor:id,name', 'category:id,name'])
            ->get();
            
        return response()->json($enrolled);
    }
    
    public function requestJoin(Request $request, $courseId)
    {
        $user = $request->user();
        $course = Course::findOrFail($courseId);
        
        // Check if already enrolled or requested
        if ($user->courses()->where('id', $courseId)->exists()) {
            return response()->json(['message' => 'Already enrolled'], 400);
        }
        
        if ($user->joinRequests()->where('course_id', $courseId)->where('status', 'PENDING')->exists()) {
             return response()->json(['message' => 'Request already pending'], 400);
        }

        $joinRequest = JoinRequest::create([
            'student_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'PENDING',
        ]);

        return response()->json($joinRequest, 201);
    }

    public function cancelJoinRequest(Request $request, $requestId)
    {
        $user = $request->user();
        $joinRequest = $user->joinRequests()->findOrFail($requestId);
        
        if ($joinRequest->status !== 'PENDING') {
            return response()->json(['message' => 'Cannot cancel processed request'], 400);
        }
        
        $joinRequest->delete();
        
        return response()->json(['message' => 'Request cancelled']);
    }
    
    public function notifications(Request $request)
    {
        return response()->json($request->user()->notifications()->orderBy('created_at', 'desc')->get());
    }
    
    public function markNotificationAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->update(['is_read' => true]);
        return response()->json($notification);
    }
    
    public function enroll(Request $request, $courseId)
    {
        try {
            $user = $request->user();
            $course = Course::findOrFail($courseId);
            
            if ($course->is_closed) {
                return response()->json(['message' => 'Course is closed for direct enrollment'], 400);
            }

            // Check if already enrolled
            if ($user->courses()->where('courses.id', $courseId)->exists()) {
                return response()->json(['message' => 'Already enrolled'], 400);
            }
            
            // Attach user to course. relying on DB defaults for enrolled_at (CURRENT_TIMESTAMP) and progress (0)
            $user->courses()->attach($courseId);
            
            // Also cancel any pending join requests for this course
            $user->joinRequests()->where('course_id', $courseId)->delete();

            return response()->json(['message' => 'Successfully enrolled', 'course' => $course], 200);
        } catch (\Exception $e) {
            \Log::error('Enrollment error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Enrollment failed: ' . $e->getMessage()], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        // Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|max:2048', // 2MB max
        ]);

        $user->name = $request->name;

        if ($request->hasFile('profile_picture')) {
            // Store locally in public/avatars
            $path = $request->file('profile_picture')->store('avatars', 'public');
            $user->profile_picture = $path;
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|confirmed|min:8',
        ]);

        $request->user()->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }

    public function getCategories()
    {
        return response()->json(Category::all());
    }

    public function showCourse(Request $request, $courseId)
    {
        $user = $request->user();
        
        // Get the course with instructor and category relationships
        $course = Course::with(['instructor', 'category'])
            ->findOrFail($courseId);
        
        // Try to load contents if the table exists, otherwise set to empty collection
        try {
            $course->load(['contents' => function($query) {
                $query->orderBy('order');
            }]);
        } catch (\Exception $e) {
            // If course_contents table doesn't exist, just set to empty collection
            $course->setRelation('contents', collect());
        }
        
        // Check if user is enrolled
        $enrollment = $user->courses()->where('courses.id', $courseId)->first();
        
        if (!$enrollment) {
            return redirect()->route('dashboard')->with('error', 'You are not enrolled in this course');
        }
        
        // Get progress from pivot
        $progress = $enrollment->pivot->progress ?? 0;
        $enrolledAt = $enrollment->pivot->enrolled_at ?? null;
        
        // Get data required by student layout
        $enrolledCourses = $user->courses()
            ->with(['instructor', 'category'])
            ->orderByPivot('enrolled_at', 'desc')
            ->take(3)
            ->get();
            
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $recommendedCourses = collect(); // Empty for course detail page
        $joinRequests = collect(); // Empty for course detail page
        $categories = Category::all();
        
        return view('student.course-show', compact('user', 'course', 'progress', 'enrolledAt', 'enrolledCourses', 'notifications', 'recommendedCourses', 'joinRequests', 'categories'));
    }
}
