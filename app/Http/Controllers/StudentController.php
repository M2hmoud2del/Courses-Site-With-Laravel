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

    public function getCategories()
    {
        return response()->json(Category::all());
    }
}
