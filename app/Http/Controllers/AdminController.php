<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\JoinRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /* ================= Dashboard ================= */

    public function dashboard()
    {
        return view('admin.dashboard', [
            'totalUsers'       => User::count(),
            'activeCourses'    => Course::where('is_closed', false)->count(),
            'pendingRequests'  => JoinRequest::where('status', 'PENDING')->count(),
            'totalEnrollments' => Enrollment::count(),

            'recentCourses' => Course::with(['instructor', 'category'])
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    /* ================= Users ================= */

    public function users()
    {
        try {
            $users = User::latest()->paginate(10);
            
            if (!$users || $users->isEmpty()) {
                return view('admin.users.index', [
                    'users' => $users,
                    'totalUsers' => 0,
                    'students' => 0,
                    'instructors' => 0,
                    'admins' => 0
                ]);
            }

            $totalUsers = User::count();
            $students   = User::where('role', 'STUDENT')->count();
            $instructors= User::where('role', 'INSTRUCTOR')->count();
            $admins     = User::where('role', 'ADMIN')->count();

            return view('admin.users.index', compact(
                'users',
                'totalUsers',
                'students',
                'instructors',
                'admins'
            ));
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Failed to load users: ' . $e->getMessage());
        }
    }

    public function createUser()
    {
        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'      => [
                    'required',
                    'string',
                    'max:255',
                    'min:2',
                    'regex:/^[a-zA-Z0-9\s\.\-_]+$/'
                ],
                'full_name' => [
                    'required',
                    'string',
                    'max:255',
                    'min:3',
                    'regex:/^[a-zA-Z\s\.\-]+$/'
                ],
                'email'     => [
                    'required',
                    'email:rfc,dns',
                    'max:255',
                    'unique:users,email'
                ],
                'password'  => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
                ],
                'password_confirmation' => 'required',
                'role'      => [
                    'required',
                    Rule::in(['STUDENT', 'INSTRUCTOR', 'ADMIN'])
                ],
                'profile_picture' => [
                    'nullable',
                    'url',
                    'active_url',
                    'max:500'
                ],
            ], [
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.',
                'name.regex' => 'Username can only contain letters, numbers, spaces, dots, dashes and underscores.',
                'full_name.regex' => 'Full name can only contain letters, spaces, dots and dashes.',
                'email.email' => 'Please enter a valid email address.',
                'profile_picture.url' => 'Please enter a valid URL for profile picture.',
            ]);

            User::create([
                'name'      => strip_tags($validated['name']),
                'full_name' => strip_tags($validated['full_name']),
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'role'      => $validated['role'],
                'profile_picture' => $validated['profile_picture'] ? filter_var($validated['profile_picture'], FILTER_VALIDATE_URL) : null,
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully.');
                
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create user: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function showUser($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new \InvalidArgumentException('Invalid user ID');
            }

            $user = User::findOrFail($id);
            
            if (!$user) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'User not found.');
            }

            return view('admin.users.show', compact('user'));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to load user details: ' . $e->getMessage());
        }
    }

    public function editUser($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new \InvalidArgumentException('Invalid user ID');
            }

            $user = User::findOrFail($id);
            
            if (!$user) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'User not found.');
            }

            return view('admin.users.edit', compact('user'));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to load user for editing: ' . $e->getMessage());
        }
    }

    public function updateUser(Request $request, $id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new \InvalidArgumentException('Invalid user ID');
            }

            $user = User::findOrFail($id);
            
            if (!$user) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'User not found.');
            }

            $validated = $request->validate([
                'name'      => [
                    'required',
                    'string',
                    'max:255',
                    'min:2',
                    'regex:/^[a-zA-Z0-9\s\.\-_]+$/'
                ],
                'full_name' => [
                    'required',
                    'string',
                    'max:255',
                    'min:3',
                    'regex:/^[a-zA-Z\s\.\-]+$/'
                ],
                'email'     => [
                    'required',
                    'email:rfc,dns',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($user->id)
                ],
                'role'      => [
                    'required',
                    Rule::in(['STUDENT', 'INSTRUCTOR', 'ADMIN'])
                ],
                'profile_picture' => [
                    'nullable',
                    'url',
                    'active_url',
                    'max:500'
                ],
            ], [
                'name.regex' => 'Username can only contain letters, numbers, spaces, dots, dashes and underscores.',
                'full_name.regex' => 'Full name can only contain letters, spaces, dots and dashes.',
                'email.email' => 'Please enter a valid email address.',
                'profile_picture.url' => 'Please enter a valid URL for profile picture.',
            ]);

            $user->update([
                'name'      => strip_tags($validated['name']),
                'full_name' => strip_tags($validated['full_name']),
                'email'     => $validated['email'],
                'role'      => $validated['role'],
                'profile_picture' => $validated['profile_picture'] ? filter_var($validated['profile_picture'], FILTER_VALIDATE_URL) : null,
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully.');
                
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update user: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function deleteUser($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new \InvalidArgumentException('Invalid user ID');
            }

            $user = User::findOrFail($id);
            
            if (!$user) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'User not found.');
            }

            // Check if user has any enrollments before deleting
            if ($user->enrollments()->exists()) {
                $enrollmentCount = $user->enrollments()->count();
                return redirect()->route('admin.users.index')
                    ->with('error', "Cannot delete user with {$enrollmentCount} active enrollment(s). Please remove enrollments first.");
            }
            
            // Check if user is instructor with courses
            if ($user->role == 'INSTRUCTOR' && $user->courses()->exists()) {
                $courseCount = $user->courses()->count();
                return redirect()->route('admin.users.index')
                    ->with('error', "Cannot delete instructor with {$courseCount} active course(s). Please reassign or delete courses first.");
            }

            // Check if user has pending join requests
            if ($user->joinRequests()->exists()) {
                $requestCount = $user->joinRequests()->count();
                return redirect()->route('admin.users.index')
                    ->with('error', "Cannot delete user with {$requestCount} pending join request(s). Please handle all requests first.");
            }

            $userName = $user->name;
            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('success', "User '{$userName}' deleted successfully.");
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /* ================= Courses ================= */

    public function courses()
    {
        try {
            $courses = Course::with(['instructor', 'category'])
                ->withCount('enrollments')
                ->paginate(10);
            
            if (!$courses) {
                return view('admin.courses.index', ['courses' => collect()]);
            }

            return view('admin.courses.index', compact('courses'));
            
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Failed to load courses: ' . $e->getMessage());
        }
    }



    public function showCourse($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new \InvalidArgumentException('Invalid course ID');
            }

            $course = Course::with(['instructor', 'category'])
                ->withCount(['enrollments', 'joinRequests'])
                ->findOrFail($id);
            
            if (!$course) {
                return redirect()->route('admin.courses.index')
                    ->with('error', 'Course not found.');
            }

            return view('admin.courses.show', compact('course'));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.courses.index')
                ->with('error', 'Course not found.');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.courses.index')
                ->with('error', 'Failed to load course details: ' . $e->getMessage());
        }
    }

    public function editCourse($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new \InvalidArgumentException('Invalid course ID');
            }

            $course = Course::findOrFail($id);
            
            if (!$course) {
                return redirect()->route('admin.courses.index')
                    ->with('error', 'Course not found.');
            }

            $categories = Category::all();
            $instructors = User::where('role', 'INSTRUCTOR')->get();
            
            if ($categories->isEmpty()) {
                return redirect()->route('admin.categories.index')
                    ->with('warning', 'Please create at least one category before editing a course.');
            }
            
            if ($instructors->isEmpty()) {
                return redirect()->route('admin.users.index')
                    ->with('warning', 'No instructors available. Please create at least one instructor.');
            }

            return view('admin.courses.edit', compact('course', 'categories', 'instructors'));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.courses.index')
                ->with('error', 'Course not found.');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.courses.index')
                ->with('error', 'Failed to load course for editing: ' . $e->getMessage());
        }
    }

    public function updateCourse(Request $request, $id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new \InvalidArgumentException('Invalid course ID');
            }

            $course = Course::findOrFail($id);
            
            if (!$course) {
                return redirect()->route('admin.courses.index')
                    ->with('error', 'Course not found.');
            }

            $validated = $request->validate([
                'title'         => [
                    'required',
                    'string',
                    'max:255',
                    'min:3',
                    'regex:/^[a-zA-Z0-9\s\-_\.\,\!\?\(\)\:]+$/'
                ],
                'description'   => [
                    'required',
                    'string',
                    'min:10',
                    'max:2000'
                ],
                'price'         => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:999999.99',
                    'regex:/^\d+(\.\d{1,2})?$/'
                ],
                'category_id'   => [
                    'required',
                    'exists:categories,id'
                ],
                'instructor_id' => [
                    'required',
                    'exists:users,id'
                ],
                'is_closed'     => [
                    'nullable',
                    'boolean'
                ],
            ], [
                'title.regex' => 'Title can only contain letters, numbers, spaces and basic punctuation.',
                'price.regex' => 'Price must be a valid number with up to 2 decimal places.',
                'price.max' => 'Price cannot exceed 999,999.99.',
                'description.min' => 'Description must be at least 10 characters.',
                'description.max' => 'Description cannot exceed 2000 characters.',
            ]);

            // Verify instructor exists and is actually an instructor
            $instructor = User::find($validated['instructor_id']);
            if (!$instructor || $instructor->role !== 'INSTRUCTOR') {
                throw ValidationException::withMessages([
                    'instructor_id' => 'Selected user is not an instructor.'
                ]);
            }

            // Verify category exists
            $category = Category::find($validated['category_id']);
            if (!$category) {
                throw ValidationException::withMessages([
                    'category_id' => 'Selected category does not exist.'
                ]);
            }

            $course->update([
                'title'         => strip_tags($validated['title']),
                'description'   => strip_tags($validated['description']),
                'price'         => number_format($validated['price'], 2, '.', ''),
                'category_id'   => $validated['category_id'],
                'instructor_id' => $validated['instructor_id'],
                'is_closed'     => $request->has('is_closed') ? $validated['is_closed'] : $course->is_closed,
            ]);

            return redirect()->route('admin.courses.index')
                ->with('success', 'Course updated successfully.');
                
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.courses.index')
                ->with('error', 'Course not found.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update course: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function deleteCourse($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new \InvalidArgumentException('Invalid course ID');
            }

            $course = Course::withCount(['enrollments', 'joinRequests'])->findOrFail($id);
            
            if (!$course) {
                return redirect()->route('admin.courses.index')
                    ->with('error', 'Course not found.');
            }



            $courseTitle = $course->title;
            $course->delete();

            return redirect()->route('admin.courses.index')
                ->with('success', "Course '{$courseTitle}' deleted successfully.");
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.courses.index')
                ->with('error', 'Course not found.');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.courses.index')
                ->with('error', 'Failed to delete course: ' . $e->getMessage());
        }
    }

    /* ================= Categories ================= */

    public function categories()
    {
        try {
            $categories = Category::withCount('courses')->latest()->get();
            
            if (!$categories) {
                return view('admin.categories.index', ['categories' => collect()]);
            }

            return view('admin.categories.index', compact('categories'));
            
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Failed to load categories: ' . $e->getMessage());
        }
    }

    public function storeCategory(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    'min:2',
                    'unique:categories,name',
                    'regex:/^[a-zA-Z0-9\s\-_]+$/'
                ],
                'description' => [
                    'nullable',
                    'string',
                    'max:500'
                ],
            ], [
                'name.regex' => 'Category name can only contain letters, numbers, spaces, dashes and underscores.',
                'name.max' => 'Category name cannot exceed 100 characters.',
                'description.max' => 'Description cannot exceed 500 characters.',
            ]);

            Category::create([
                'name' => strip_tags($validated['name']),
                'description' => $validated['description'] ? strip_tags($validated['description']) : null,
            ]);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully.');
                
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create category: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updateCategory(Request $request, $id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new \InvalidArgumentException('Invalid category ID');
            }

            $category = Category::findOrFail($id);
            
            if (!$category) {
                return redirect()->route('admin.categories.index')
                    ->with('error', 'Category not found.');
            }

            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    'min:2',
                    Rule::unique('categories', 'name')->ignore($category->id),
                    'regex:/^[a-zA-Z0-9\s\-_]+$/'
                ],
                'description' => [
                    'nullable',
                    'string',
                    'max:500'
                ],
            ], [
                'name.regex' => 'Category name can only contain letters, numbers, spaces, dashes and underscores.',
                'name.max' => 'Category name cannot exceed 100 characters.',
                'description.max' => 'Description cannot exceed 500 characters.',
            ]);

            $category->update([
                'name' => strip_tags($validated['name']),
                'description' => $validated['description'] ? strip_tags($validated['description']) : null,
            ]);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category updated successfully.');
                
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Category not found.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update category: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function deleteCategory($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new \InvalidArgumentException('Invalid category ID');
            }

            $category = Category::withCount('courses')->findOrFail($id);
            
            if (!$category) {
                return redirect()->route('admin.categories.index')
                    ->with('error', 'Category not found.');
            }

            // Check if category has courses
            if ($category->courses_count > 0) {
                return redirect()->route('admin.categories.index')
                    ->with('error', "Cannot delete category with {$category->courses_count} course(s). Please delete or move the courses first.");
            }
            
            $categoryName = $category->name;
            $category->delete();
            
            return redirect()->route('admin.categories.index')
                ->with('success', "Category '{$categoryName}' deleted successfully.");
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Category not found.');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }

    /* ================= Statistics ================= */

    public function statistics()
    {
        try {
            $totalUsers = User::count();
            $students = User::where('role', 'STUDENT')->count();
            $instructors = User::where('role', 'INSTRUCTOR')->count();
            $courses = Course::count();
            $activeCourses = Course::where('is_closed', false)->count();
            $closedCourses = Course::where('is_closed', true)->count();

            return view('admin.statistics', compact(
                'totalUsers',
                'students',
                'instructors',
                'courses',
                'activeCourses',
                'closedCourses'
            ));
            
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Failed to load statistics: ' . $e->getMessage());
        }
    }
}