<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\JoinRequest;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class InstructorController extends Controller
{
    public function dashboard()
    {
        $instructorId = Auth::id();

        // Total courses by instructor
        $totalCourses = Course::where('instructor_id', $instructorId)->count();

        // Open (published) courses
        $publishedCourses = Course::where('instructor_id', $instructorId)
            ->where('is_closed', false)
            ->count();

        // Pending join requests
        $pendingEnrollments = JoinRequest::whereHas('course', function ($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        })
            ->where('status', 'PENDING')
            ->count();

        // Total students across all instructor courses
        $totalStudents = Enrollment::whereHas('course', function ($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        })
            ->distinct('student_id')
            ->count('student_id');

        // Recent courses
        $recentCourses = Course::where('instructor_id', $instructorId)
            ->with('category')
            ->latest()
            ->take(5)
            ->get();

        $recentNotifications = Notification::where('recipient_id', $instructorId)
            ->latest()
            ->take(5)
            ->get();

        return view('instructor.dashboard', compact(
            'totalCourses',
            'publishedCourses',
            'pendingEnrollments',
            'totalStudents',
            'recentCourses',
            'recentNotifications'
        ));
    }

    public function courses()
    {
        $instructorId = Auth::id();

        try {
            $courses = Course::where('instructor_id', $instructorId)
                ->withCount('enrollments')
                ->latest()
                ->paginate(10);

            return view('instructor.courses.index', compact('courses'));
        } catch (\Exception $e) {
            return redirect()->route('instructor.dashboard')
                ->with('error', 'Failed to load courses: ' . $e->getMessage());
        }
    }

    public function createCourse()
    {
        try {
            $categories = Category::all();

            if ($categories->isEmpty()) {
                return redirect()->route('instructor.courses.index')
                    ->with('warning', 'Please create at least one category before adding a course.');
            }

            return view('instructor.courses.create', compact('categories'));
        } catch (\Exception $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Failed to load course creation form: ' . $e->getMessage());
        }
    }

    public function storeCourse(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'       => [
                    'required',
                    'string',
                    'max:255',
                    'min:3',
                    'regex:/^[a-zA-Z0-9\s\-_\.\,\!\?\(\)\:]+$/'
                ],
                'description' => [
                    'required',
                    'string',
                    'min:10',
                    'max:2000'
                ],
                'price'       => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:999999.99',
                    'regex:/^\d+(\.\d{1,2})?$/'
                ],
                'category_id' => [
                    'required',
                    'exists:categories,id'
                ],
                'is_closed'   => [
                    'nullable',
                    'boolean'
                ],
            ], [
                'title.regex' => 'Title can only contain letters, numbers, spaces and basic punctuation.',
                'price.regex' => 'Price must be a valid number with up to 2 decimal places.',
                'price.max'   => 'Price cannot exceed 999,999.99.',
                'description.min' => 'Description must be at least 10 characters.',
                'description.max' => 'Description cannot exceed 2000 characters.',
            ]);

            // Assign the logged-in instructor as the course instructor
            $instructor = Auth::user();
            if (!$instructor || $instructor->role !== 'INSTRUCTOR') {
                throw ValidationException::withMessages([
                    'instructor' => 'You are not authorized to create courses.'
                ]);
            }

            // Verify category exists
            $category = Category::find($validated['category_id']);
            if (!$category) {
                throw ValidationException::withMessages([
                    'category_id' => 'Selected category does not exist.'
                ]);
            }

            Course::create([
                'title'         => strip_tags($validated['title']),
                'description'   => strip_tags($validated['description']),
                'price'         => number_format($validated['price'], 2, '.', ''),
                'category_id'   => $validated['category_id'],
                'instructor_id' => $instructor->id,
                'is_closed'     => $request->has('is_closed') ? $validated['is_closed'] : false,
            ]);

            return redirect()->route('instructor.courses.index')
                ->with('success', 'Course created successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create course: ' . $e->getMessage())
                ->withInput();
        }
    }


    public function showCourse($id)
    {
        $instructorId = Auth::id();

        try {
            $course = Course::where('instructor_id', $instructorId)
                ->with(['enrollments.student'])
                ->findOrFail($id);

            return view('instructor.courses.show', compact('course'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Course not found or you do not have access.');
        } catch (\Exception $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Failed to load course details: ' . $e->getMessage());
        }
    }

    public function editCourse($id)
    {
        $instructorId = Auth::id();

        try {
            $course = Course::where('instructor_id', $instructorId)
                ->withCount('enrollments')
                ->with('enrollments')
                ->findOrFail($id);

            $categories = Category::all();

            if ($categories->isEmpty()) {
                return redirect()->route('instructor.courses.index')
                    ->with('warning', 'Please create at least one category before editing a course.');
            }

            return view('instructor.courses.edit', compact('course', 'categories'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Course not found or you do not have access.');
        } catch (\Exception $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Failed to load course edit form: ' . $e->getMessage());
        }
    }

    public function updateCourse(Request $request, $id)
    {
        $instructorId = Auth::id();

        try {
            $course = Course::where('instructor_id', $instructorId)->findOrFail($id);

            $validated = $request->validate([
                'title'       => 'required|string|min:3|max:255|regex:/^[a-zA-Z0-9\s\-_\.\,\!\?\(\)\:]+$/',
                'description' => 'required|string|min:10|max:2000',
                'price'       => 'required|numeric|min:0|max:999999.99|regex:/^\d+(\.\d{1,2})?$/',
                'is_closed'   => 'nullable|boolean',
            ]);

            $course->update([
                'title'       => strip_tags($validated['title']),
                'description' => strip_tags($validated['description']),
                'price'       => number_format($validated['price'], 2, '.', ''),
                'is_closed'   => $request->has('is_closed') ? $validated['is_closed'] : $course->is_closed,
            ]);

            return redirect()->route('instructor.courses.index')
                ->with('success', 'Course updated successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update course: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function deleteCourse($id)
    {
        $instructorId = Auth::id();

        try {
            $course = Course::where('instructor_id', $instructorId)
                ->withCount('enrollments')
                ->findOrFail($id);

            if ($course->enrollments_count > 0) {
                return redirect()->route('instructor.courses.index')
                    ->with('error', "Cannot delete course with {$course->enrollments_count} enrollments. Remove enrollments first.");
            }

            $courseTitle = $course->title;
            $course->delete();

            return redirect()->route('instructor.courses.index')
                ->with('success', "Course '{$courseTitle}' deleted successfully.");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Course not found or you do not have access.');
        } catch (\Exception $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Failed to delete course: ' . $e->getMessage());
        }
    }

    // Add these methods to your existing InstructorController.php

    /* ================= Join Requests Management ================= */
    public function joinRequests()
    {
        $instructorId = Auth::id();

        try {
            $joinRequests = JoinRequest::whereHas('course', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })
                ->with(['student', 'course'])
                ->where('status', 'PENDING')
                ->latest()
                ->paginate(10);

            return view('instructor.join-requests.index', compact('joinRequests'));
        } catch (\Exception $e) {
            return redirect()->route('instructor.dashboard')
                ->with('error', 'Failed to load join requests: ' . $e->getMessage());
        }
    }

    public function approveJoinRequest($id)
    {
        $instructorId = Auth::id();

        try {
            $joinRequest = JoinRequest::whereHas('course', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })
                ->with(['student', 'course'])
                ->findOrFail($id);

            if ($joinRequest->status !== 'PENDING') {
                return redirect()->back()
                    ->with('error', 'This request has already been processed.');
            }

            // Update join request status
            $joinRequest->update(['status' => 'ACCEPTED']);

            // Create enrollment
            Enrollment::create([
                'student_id' => $joinRequest->student_id,
                'course_id' => $joinRequest->course_id,
                'enrolled_at' => now(),
                'progress' => 0,
            ]);

            // Create notification for student
            Notification::create([
                'recipient_id' => $joinRequest->student_id,
                'message' => "Your join request for course '{$joinRequest->course->title}' has been approved.",
                'date' => now(),
                'is_read' => false,
            ]);

            return redirect()->route('instructor.join-requests.index')
                ->with('success', 'Join request approved successfully.');
        } catch (\Exception $e) {
            return redirect()->route('instructor.join-requests.index')
                ->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    public function rejectJoinRequest($id)
    {
        $instructorId = Auth::id();

        try {
            $joinRequest = JoinRequest::whereHas('course', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })
                ->with(['student', 'course'])
                ->findOrFail($id);

            if ($joinRequest->status !== 'PENDING') {
                return redirect()->back()
                    ->with('error', 'This request has already been processed.');
            }

            $joinRequest->update(['status' => 'REJECTED']);

            // Create notification for student
            Notification::create([
                'recipient_id' => $joinRequest->student_id,
                'message' => "Your join request for course '{$joinRequest->course->title}' has been rejected.",
                'date' => now(),
                'is_read' => false,
            ]);

            return redirect()->route('instructor.join-requests.index')
                ->with('success', 'Join request rejected successfully.');
        } catch (\Exception $e) {
            return redirect()->route('instructor.join-requests.index')
                ->with('error', 'Failed to reject request: ' . $e->getMessage());
        }
    }

    /* ================= Enrollments Management ================= */
    public function enrollments()
    {
        $instructorId = Auth::id();

        try {
            $enrollments = Enrollment::whereHas('course', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })
                ->with(['student', 'course'])
                ->latest()
                ->paginate(15);

            $totalEnrollments = Enrollment::whereHas('course', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })->count();

            return view('instructor.enrollments.index', compact('enrollments', 'totalEnrollments'));
        } catch (\Exception $e) {
            return redirect()->route('instructor.dashboard')
                ->with('error', 'Failed to load enrollments: ' . $e->getMessage());
        }
    }

    public function removeEnrollment($id)
    {
        $instructorId = Auth::id();

        try {
            $enrollment = Enrollment::whereHas('course', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })
                ->findOrFail($id);

            $studentName = $enrollment->student->name;
            $courseTitle = $enrollment->course->title;

            $enrollment->delete();

            // Create notification for student
            Notification::create([
                'recipient_id' => $enrollment->student_id,
                'message' => "You have been removed from the course '{$courseTitle}'.",
                'date' => now(),
                'is_read' => false,
            ]);

            return redirect()->route('instructor.enrollments.index')
                ->with('success', "Student '{$studentName}' removed from course '{$courseTitle}' successfully.");
        } catch (\Exception $e) {
            return redirect()->route('instructor.enrollments.index')
                ->with('error', 'Failed to remove enrollment: ' . $e->getMessage());
        }
    }

    /* ================= Analytics Dashboard ================= */
    public function analytics()
    {
        $instructorId = Auth::id();

        try {
            // Total courses statistics
            $coursesStats = Course::where('instructor_id', $instructorId)
                ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_closed = 1 THEN 1 ELSE 0 END) as closed,
                SUM(CASE WHEN is_closed = 0 THEN 1 ELSE 0 END) as open
            ')
                ->first();

            // Enrollment statistics
            $enrollmentStats = Enrollment::whereHas('course', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })
                ->selectRaw('
                COUNT(*) as total,
                AVG(progress) as avg_progress,
                MAX(progress) as max_progress,
                MIN(progress) as min_progress
            ')
                ->first();

            // Revenue statistics
            $revenueStats = Course::where('instructor_id', $instructorId)
                ->withCount('enrollments')
                ->get()
                ->map(function ($course) {
                    return $course->price * $course->enrollments_count;
                });

            $totalRevenue = $revenueStats->sum();
            $avgRevenuePerCourse = $revenueStats->avg();

            // Top performing courses
            $topCourses = Course::where('instructor_id', $instructorId)
                ->withCount('enrollments')
                ->orderBy('enrollments_count', 'desc')
                ->take(5)
                ->get();

            // Monthly enrollment trends
            $monthlyEnrollments = Enrollment::whereHas('course', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })
                ->selectRaw('
                YEAR(enrolled_at) as year,
                MONTH(enrolled_at) as month,
                COUNT(*) as count
            ')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->take(6)
                ->get()
                ->reverse();

            // Get all courses with enrollment stats for detailed table
            $courses = Course::where('instructor_id', $instructorId)
                ->withCount('enrollments')
                ->withAvg('enrollments', 'progress')
                ->get();

            return view('instructor.analytics.index', compact(
                'coursesStats',
                'enrollmentStats',
                'totalRevenue',
                'avgRevenuePerCourse',
                'topCourses',
                'monthlyEnrollments',
                'courses' // Add this
            ));
        } catch (\Exception $e) {
            return redirect()->route('instructor.dashboard')
                ->with('error', 'Failed to load analytics: ' . $e->getMessage());
        }
    }

    /* ================= Students in My Courses ================= */
    public function students($courseId)
    {
        $instructorId = Auth::id();

        try {
            $course = Course::where('instructor_id', $instructorId)
                ->with('enrollments.student')
                ->findOrFail($courseId);

            return view('instructor.courses.students', compact('course'));
        } catch (\Exception $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Failed to load students: ' . $e->getMessage());
        }
    }
}
