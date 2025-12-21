<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ContentController extends Controller
{
    public function index($courseId)
    {
        $instructorId = Auth::id();

        try {
            $course = Course::where('instructor_id', $instructorId)
                ->with(['contents' => function($query) {
                    $query->orderBy('order');
                }])
                ->findOrFail($courseId);

            return view('instructor.content.index', compact('course'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Course not found or you do not have access.');
        } catch (\Exception $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Failed to load course content: ' . $e->getMessage());
        }
    }

    public function create($courseId)
    {
        $instructorId = Auth::id();

        try {
            $course = Course::where('instructor_id', $instructorId)
                ->findOrFail($courseId);

            // Get next order number
            $nextOrder = CourseContent::where('course_id', $courseId)->max('order') + 1;

            return view('instructor.content.create', compact('course', 'nextOrder'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Course not found or you do not have access.');
        } catch (\Exception $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Failed to load content creation form: ' . $e->getMessage());
        }
    }

    public function store(Request $request, $courseId)
    {
        $instructorId = Auth::id();

        try {
            // Verify course belongs to instructor
            $course = Course::where('instructor_id', $instructorId)
                ->findOrFail($courseId);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'content_type' => 'required|in:LESSON,VIDEO,DOCUMENT,LINK',
                'external_link' => 'nullable|url|max:500',
                'content' => 'nullable|string',
                'order' => 'required|integer|min:0',
            ]);

            // Validate external link for VIDEO, DOCUMENT, and LINK types
            if (in_array($validated['content_type'], ['VIDEO', 'DOCUMENT', 'LINK']) && empty($validated['external_link'])) {
                throw ValidationException::withMessages([
                    'external_link' => 'External link is required for ' . strtolower($validated['content_type']) . ' content.'
                ]);
            }

            // Validate content for LESSON type
            if ($validated['content_type'] === 'LESSON' && empty($validated['content'])) {
                throw ValidationException::withMessages([
                    'content' => 'Content is required for lessons.'
                ]);
            }

            $content = CourseContent::create([
                'course_id' => $courseId,
                'title' => strip_tags($validated['title']),
                'description' => strip_tags($validated['description'] ?? ''),
                'content_type' => $validated['content_type'],
                'external_link' => $validated['external_link'] ?? null,
                'content' => $validated['content'] ?? null,
                'order' => $validated['order'] - 1,
            ]);

            return redirect()->route('instructor.content.index', $courseId)
                ->with('success', 'Content added successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create content: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($courseId, $contentId)
    {
        $instructorId = Auth::id();

        try {
            $course = Course::where('instructor_id', $instructorId)
                ->findOrFail($courseId);

            $content = CourseContent::where('course_id', $courseId)
                ->findOrFail($contentId);

            return view('instructor.content.edit', compact('course', 'content'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Content not found or you do not have access.');
        } catch (\Exception $e) {
            return redirect()->route('instructor.content.index', $courseId)
                ->with('error', 'Failed to load content edit form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $courseId, $contentId)
    {
        $instructorId = Auth::id();

        try {
            // Verify course belongs to instructor
            $course = Course::where('instructor_id', $instructorId)
                ->findOrFail($courseId);

            $content = CourseContent::where('course_id', $courseId)
                ->findOrFail($contentId);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'content_type' => 'required|in:LESSON,VIDEO,DOCUMENT,LINK',
                'external_link' => 'nullable|url|max:500',
                'content' => 'nullable|string',
                'order' => 'required|integer|min:0',
            ]);

            // Validate external link for VIDEO, DOCUMENT, and LINK types
            if (in_array($validated['content_type'], ['VIDEO', 'DOCUMENT', 'LINK']) && empty($validated['external_link'])) {
                throw ValidationException::withMessages([
                    'external_link' => 'External link is required for ' . strtolower($validated['content_type']) . ' content.'
                ]);
            }

            // Validate content for LESSON type
            if ($validated['content_type'] === 'LESSON' && empty($validated['content'])) {
                throw ValidationException::withMessages([
                    'content' => 'Content is required for lessons.'
                ]);
            }

            $content->update([
                'title' => strip_tags($validated['title']),
                'description' => strip_tags($validated['description'] ?? ''),
                'content_type' => $validated['content_type'],
                'external_link' => $validated['external_link'] ?? null,
                'content' => $validated['content'] ?? null,
                'order' => $validated['order'] - 1,
            ]);

            return redirect()->route('instructor.content.index', $courseId)
                ->with('success', 'Content updated successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update content: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($courseId, $contentId)
    {
        $instructorId = Auth::id();

        try {
            // Verify course belongs to instructor
            $course = Course::where('instructor_id', $instructorId)
                ->findOrFail($courseId);

            $content = CourseContent::where('course_id', $courseId)
                ->findOrFail($contentId);

            $contentTitle = $content->title;
            $content->delete();

            return redirect()->route('instructor.content.index', $courseId)
                ->with('success', "Content '{$contentTitle}' deleted successfully.");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Content not found or you do not have access.');
        } catch (\Exception $e) {
            return redirect()->route('instructor.content.index', $courseId)
                ->with('error', 'Failed to delete content: ' . $e->getMessage());
        }
    }

    // Reorder contents
    public function reorder(Request $request, $courseId)
    {
        $instructorId = Auth::id();

        try {
            // Verify course belongs to instructor
            $course = Course::where('instructor_id', $instructorId)
                ->findOrFail($courseId);

            $request->validate([
                'order' => 'required|array',
                'order.*' => 'integer|exists:course_contents,id',
            ]);

            foreach ($request->order as $position => $contentId) {
                CourseContent::where('id', $contentId)
                    ->where('course_id', $courseId)
                    ->update(['order' => $position]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}