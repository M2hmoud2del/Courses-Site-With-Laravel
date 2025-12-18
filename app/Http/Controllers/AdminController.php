<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\JoinRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
    // Use pagination instead of get()
    $users = User::latest()->paginate(10);

    // Counts
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
}

    public function createUser()
    {
        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
            'role'      => 'required|in:STUDENT,INSTRUCTOR,ADMIN',
            'profile_picture' => 'nullable|url',
        ]);

        User::create([
            'name'      => $request->name,
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'profile_picture' => $request->profile_picture,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully');
    }

    public function showUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'      => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'role'      => 'required|in:STUDENT,INSTRUCTOR,ADMIN',
        ]);

        $user->update($request->only('name', 'full_name', 'email', 'role'));

    return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
    }

    public function deleteUser($id)
    {
        User::findOrFail($id)->delete();
    return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }

    /* ================= Courses ================= */

    public function courses()
    {
        $courses = Course::with(['instructor', 'category'])->paginate(10);
        return view('admin.courses.index', compact('courses'));
    }

    public function createCourse()
    {
        return view('admin.courses.create', [
            'categories'  => Category::all(),
            'instructors' => User::where('role', 'INSTRUCTOR')->get(),
        ]);
    }

    public function storeCourse(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'required',
            'price'         => 'required|numeric|min:0',
            'category_id'   => 'required|exists:categories,id',
            'instructor_id' => 'required|exists:users,id',
        ]);

        Course::create($request->all());

    return redirect()->route('admin.courses.index')->with('success', 'Course created successfully');
    }

    public function showCourse($id)
    {
        $course = Course::with(['instructor', 'category'])->findOrFail($id);
        return view('admin.courses.show', compact('course'));
    }

    public function editCourse($id)
    {
        return view('admin.courses.edit', [
            'course'      => Course::findOrFail($id),
            'categories'  => Category::all(),
            'instructors' => User::where('role', 'INSTRUCTOR')->get(),
        ]);
    }

    public function updateCourse(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'required',
            'price'         => 'required|numeric|min:0',
            'category_id'   => 'required|exists:categories,id',
            'instructor_id' => 'required|exists:users,id',
            'is_closed'     => 'boolean',
        ]);

        $course->update($request->all());

    return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully');
    }

    public function deleteCourse($id)
    {
        Course::findOrFail($id)->delete();
    return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully');
    }

    /* ================= Categories ================= */

    public function categories()
    {
        $categories = Category::withCount('courses')->latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        Category::create($request->only('name', 'description'));

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully');
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($request->only('name', 'description'));

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully');
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        
        // Check if category has courses
        if ($category->courses()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with existing courses. Please delete or move the courses first.');
        }
        
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully');
    }

        /* ================= Statistics ================= */

    public function statistics()
    {
        return view('admin.statistics', [
            'totalUsers'    => User::count(),
            'students'      => User::where('role', 'STUDENT')->count(),
            'instructors'   => User::where('role', 'INSTRUCTOR')->count(),
            'courses'       => Course::count(),
            'activeCourses' => Course::where('is_closed', false)->count(),
            'draftCourses'  => 0, // Add this field to your courses table if needed
            'closedCourses' => Course::where('is_closed', true)->count(),
        ]);
    }
}
