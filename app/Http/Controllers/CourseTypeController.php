<?php

namespace App\Http\Controllers;

use App\Models\CourseType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseTypeController extends Controller
{
    public function index(): View
    {
        $courseTypes = CourseType::withCount('schoolClasses')->orderBy('name')->paginate(15);

        return view('course-types.index', compact('courseTypes'));
    }

    public function create(): View
    {
        return view('course-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:course_types,name'],
        ]);

        CourseType::create($validated);

        return redirect()->route('course-types.index')->with('success', 'Course type created.');
    }

    public function edit(CourseType $courseType): View
    {
        return view('course-types.edit', compact('courseType'));
    }

    public function update(Request $request, CourseType $courseType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('course_types', 'name')->ignore($courseType->id)],
        ]);

        $courseType->update($validated);

        return redirect()->route('course-types.index')->with('success', 'Course type updated.');
    }

    public function destroy(CourseType $courseType): RedirectResponse
    {
        if ($courseType->schoolClasses()->exists()) {
            return redirect()->route('course-types.index')->withErrors([
                'course_type' => 'Cannot delete a course type that is still assigned to classes.',
            ]);
        }

        $courseType->delete();

        return redirect()->route('course-types.index')->with('success', 'Course type deleted.');
    }
}
