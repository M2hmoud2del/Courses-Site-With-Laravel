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
            // ->take(3)
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
        try {
            $user = $request->user();
            $course = Course::findOrFail($courseId);
            
            // Check if already enrolled
            if ($user->courses()->where('courses.id', $courseId)->exists()) {
                return response()->json(['message' => 'Already enrolled'], 400);
            }
            
            // Check for existing request (any status)
            $existingRequest = JoinRequest::where('student_id', $user->id)
                ->where('course_id', $courseId)
                ->first();

            if ($existingRequest) {
                if ($existingRequest->status === 'PENDING') {
                    return response()->json(['message' => 'Request already pending'], 400);
                }
                
                // If rejected or any other status, update it back to pending
                $existingRequest->update([
                    'status' => 'PENDING',
                    'request_date' => now(),
                ]);

                return response()->json($existingRequest, 200);
            }

            $joinRequest = JoinRequest::create([
                'student_id' => $user->id,
                'course_id' => $course->id,
                'request_date' => now(),
                'status' => 'PENDING',
            ]);

            return response()->json($joinRequest, 201);
        } catch (\Exception $e) {
            \Log::error('Join request error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'course_id' => $courseId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to create join request',
                'error' => $e->getMessage()
            ], 500);
        }
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

        // Get completion data
        $completedContentIds = $user->completedContents()
            ->where('course_content_completions.course_id', $courseId)
            ->pluck('course_contents.id')
            ->toArray();
            
        // Determine current content (first uncompleted or first content if all uncompleted, or last if all completed)
        $contents = $course->contents;
        $currentContent = null;
        
        if ($contents->isNotEmpty()) {
            // Find first uncompleted
            $currentContent = $contents->first(function ($content) use ($completedContentIds) {
                return !in_array($content->id, $completedContentIds);
            });
            
            // If all completed, maybe show the first one or the last one? Let's show the first one for review
            if (!$currentContent) {
                $currentContent = $contents->first();
            }
        }
        
        // Get data required by student layout
        $enrolledCourses = $user->courses()
            ->with(['instructor', 'category'])
            ->orderByPivot('enrolled_at', 'desc')
            // ->take(3)
            ->get();
            
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $recommendedCourses = collect(); // Empty for course detail page
        $joinRequests = collect(); // Empty for course detail page
        $categories = Category::all();
        
        return view('student.course-show', compact('user', 'course', 'progress', 'enrolledAt', 'enrolledCourses', 'notifications', 'recommendedCourses', 'joinRequests', 'categories', 'currentContent', 'completedContentIds'));
    }

    public function markContentComplete(Request $request, $courseId, $contentId)
    {
        $user = $request->user();
        
        // Verify enrollment
        $enrollment = $user->courses()->where('courses.id', $courseId)->first();
        if (!$enrollment) {
            return response()->json(['message' => 'Not enrolled'], 403);
        }

        // Verify content belongs to course
        $content = \App\Models\CourseContent::where('course_id', $courseId)
            ->where('id', $contentId)
            ->firstOrFail();

        // Mark as complete if not already
        \App\Models\CourseContentCompletion::firstOrCreate([
            'user_id' => $user->id,
            'course_content_id' => $contentId
        ], [
            'course_id' => $courseId,
            'completed_at' => now()
        ]);

        // Calculate new progress
        $totalContent = \App\Models\CourseContent::where('course_id', $courseId)->count();
        $completedContent = \App\Models\CourseContentCompletion::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->count();
            
        $progress = $totalContent > 0 ? round(($completedContent / $totalContent) * 100) : 0;
        
        // Update enrollment progress
        $user->courses()->updateExistingPivot($courseId, ['progress' => $progress]);

        // Find next content
        $nextContent = \App\Models\CourseContent::where('course_id', $courseId)
            ->where('order', '>', $content->order)
            ->orderBy('order', 'asc')
            ->first();

        // Prepare response data
        $responseData = [
            'message' => 'Content marked as complete',
            'progress' => $progress,
            'completed_content_count' => $completedContent
        ];

        if ($nextContent) {
            $responseData['next_content'] = [
                'id' => $nextContent->id,
                'title' => $nextContent->title,
                'type_label' => $nextContent->type_label,
                'type_icon' => $nextContent->type_icon,
                'content_type' => $nextContent->content_type,
                'embed_url' => $nextContent->embed_url,
                'external_link' => $nextContent->external_link,
                'platform' => $nextContent->platform,
                'content' => nl2br(e($nextContent->content))
            ];
        } else {
            $responseData['course_completed'] = true;
        }

        return response()->json($responseData);
    }
}
