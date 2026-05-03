<?php

namespace App\Http\Controllers;

use App\Models\CourseType;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SchoolClass::class);

        $query = SchoolClass::query()
            ->with('courseType')
            ->withCount(['students', 'subjects']);

        if ($request->user()->role === 'teacher') {
            $query->forTeacher($request->user());
        }

        $query->when($request->filled('course_type_id'), fn ($q) => $q->where('course_type_id', $request->course_type_id))
            ->when($request->filled('shift'), fn ($q) => $q->where('shift', $request->shift))
            ->when($request->filled('class_time'), fn ($q) => $q->where('class_time', $request->class_time))
            ->when($request->filled('is_active'), function ($q) use ($request) {
                if ($request->is_active === '1') {
                    $q->where('is_active', true);
                } elseif ($request->is_active === '0') {
                    $q->where('is_active', false);
                }
            })
            ->when($request->filled('search'), fn ($q) => $q->where('class_name', 'like', '%'.$request->search.'%'))
            ->orderBy('class_name')
            ->orderBy('class_time');

        $classes = $query->paginate(12)->withQueryString();

        $courseTypes = CourseType::orderBy('name')->get();
        $timeOptions = SchoolClass::query()
            ->when($request->user()->role === 'teacher', fn ($q) => $q->forTeacher($request->user()))
            ->whereNotNull('class_time')
            ->distinct()
            ->orderBy('class_time')
            ->pluck('class_time');

        return view('classes.index', compact('classes', 'courseTypes', 'timeOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', SchoolClass::class);

        $courseTypes = CourseType::orderBy('name')->get();
        $teachers = User::query()->where('role', 'teacher')->orderBy('name')->get();

        return view('classes.create', compact('courseTypes', 'teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SchoolClass::class);

        $validated = $request->validate([
            'class_name' => ['required', 'string', 'max:255'],
            'course_type_id' => ['required', 'exists:course_types,id'],
            'start_date' => ['required', 'date'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:120'],
            'class_time' => ['required', 'date_format:H:i'],
            'classroom' => ['nullable', 'string', 'max:255'],
            'monthly_fee_amount' => ['required', 'numeric', 'min:0'],
            'shift' => ['required', 'string', 'in:morning,afternoon,evening'],
            'is_active' => ['sometimes', 'boolean'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['class_time'] = self::normalizeTimeToSql($validated['class_time']);

        $teacherIds = User::query()
            ->where('role', 'teacher')
            ->whereIn('id', $validated['teacher_ids'] ?? [])
            ->pluck('id')
            ->all();

        unset($validated['teacher_ids']);

        $class = SchoolClass::create($validated);
        $class->teachers()->sync($teacherIds);

        return redirect()->route('classes.index')->with('success', 'Class created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolClass $class): View
    {
        $this->authorize('view', $class);

        $class->load(['courseType', 'teachers', 'subjects', 'students']);

        return view('classes.show', ['schoolClass' => $class]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolClass $class): View
    {
        $this->authorize('update', $class);

        $class->load('teachers');

        $courseTypes = CourseType::orderBy('name')->get();
        $teachers = User::query()->where('role', 'teacher')->orderBy('name')->get();

        return view('classes.edit', ['schoolClass' => $class, 'courseTypes' => $courseTypes, 'teachers' => $teachers]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $this->authorize('update', $class);

        $validated = $request->validate([
            'class_name' => ['required', 'string', 'max:255'],
            'course_type_id' => ['required', 'exists:course_types,id'],
            'start_date' => ['required', 'date'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:120'],
            'class_time' => ['required', 'date_format:H:i'],
            'classroom' => ['nullable', 'string', 'max:255'],
            'monthly_fee_amount' => ['required', 'numeric', 'min:0'],
            'shift' => ['required', 'string', 'in:morning,afternoon,evening'],
            'is_active' => ['sometimes', 'boolean'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['class_time'] = self::normalizeTimeToSql($validated['class_time']);

        $teacherIds = User::query()
            ->where('role', 'teacher')
            ->whereIn('id', $validated['teacher_ids'] ?? [])
            ->pluck('id')
            ->all();

        unset($validated['teacher_ids']);

        $class->update($validated);
        $class->teachers()->sync($teacherIds);

        return redirect()->route('classes.index')->with('success', 'Class updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SchoolClass $class): RedirectResponse
    {
        $this->authorize('delete', $class);

        if ($class->students()->exists()) {
            return redirect()->route('classes.index')->withErrors([
                'class' => 'Cannot delete a class that still has students. Reassign or remove students first.',
            ]);
        }

        $class->delete();

        return redirect()->route('classes.index')->with('success', 'Class deleted.');
    }

    private static function normalizeTimeToSql(string $hi): string
    {
        // HTML time input is H:i; DB column is TIME (H:i:s).
        return strlen($hi) === 5 ? $hi.':00' : $hi;
    }
}
