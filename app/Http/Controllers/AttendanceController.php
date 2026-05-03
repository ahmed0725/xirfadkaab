<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $allowedClassIds = $this->allowedSchoolClassIds($user);

        $attendances = Attendance::with(['student', 'schoolClass.courseType', 'subject'])
            ->when($request->date, fn ($query, $date) => $query->whereDate('date', $date))
            ->when($request->school_class_id, fn ($query, $classId) => $query->where('school_class_id', $classId))
            ->when($request->student_id, fn ($query, $studentId) => $query->where('student_id', $studentId))
            ->when($user->role === 'teacher', fn ($query) => $query->whereIn('school_class_id', $allowedClassIds))
            ->latest('date')
            ->paginate(20)
            ->withQueryString();

        $classes = $this->classListForFilters($user);

        return view('attendance.index', compact('attendances', 'classes'));
    }

    public function create(): View
    {
        $user = request()->user();
        $classes = $this->classListForMarking($user);

        return view('attendance.mark', compact('classes'));
    }

    public function classData(Request $request): JsonResponse
    {
        $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
        ]);

        $class = SchoolClass::query()
            ->with(['courseType', 'students' => fn ($q) => $q->orderBy('name'), 'subjects' => fn ($q) => $q->orderBy('subject_name')])
            ->findOrFail($request->school_class_id);

        $this->authorize('view', $class);

        return response()->json([
            'class' => [
                'id' => $class->id,
                'display_name' => $class->display_name,
            ],
            'students' => $class->students->map(fn (Student $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'student_id' => $s->student_id,
            ])->values(),
            'subjects' => $class->subjects->map(fn (Subject $sub) => [
                'id' => $sub->id,
                'subject_name' => $sub->subject_name,
            ])->values(),
        ]);
    }

    public function edit(Attendance $attendance): View
    {
        $this->authorize('view', $attendance->schoolClass);

        $user = request()->user();
        $classes = SchoolClass::query()
            ->with('courseType')
            ->with([
                'students' => fn ($q) => $q->orderBy('name'),
                'subjects' => fn ($q) => $q->orderBy('subject_name'),
            ])
            ->when($user->role === 'teacher', fn ($q) => $q->forTeacher($user))
            ->orderBy('class_name')
            ->orderBy('class_time')
            ->get();

        return view('attendance.edit', compact('attendance', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->canManageAttendanceForClass((int) $request->input('school_class_id'))) {
            abort(403);
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'exists:students,id'],
            'records.*.status' => ['required', 'in:present,absent,late'],
            'records.*.note' => ['nullable', 'string'],
        ]);

        // Normalize optional subject_id ('' => null) so updateOrCreate keys behave correctly.
        $validated['subject_id'] = $validated['subject_id'] ?: null;

        if (! empty($validated['subject_id'])) {
            $subjectBelongsToClass = Subject::query()
                ->whereKey($validated['subject_id'])
                ->where('school_class_id', $validated['school_class_id'])
                ->exists();

            if (! $subjectBelongsToClass) {
                return back()->withErrors(['subject_id' => 'Selected subject does not belong to the selected class.'])->withInput();
            }
        }

        foreach ($validated['records'] as $record) {
            $studentBelongsToClass = Student::query()
                ->whereKey($record['student_id'])
                ->where('school_class_id', $validated['school_class_id'])
                ->exists();

            if (! $studentBelongsToClass) {
                continue;
            }

            Attendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'date' => $validated['date'],
                    'subject_id' => $validated['subject_id'] ?? null,
                ],
                [
                    'school_class_id' => $validated['school_class_id'],
                    'status' => $record['status'],
                    'note' => $record['note'] ?? null,
                ]
            );
        }

        return redirect()->route('attendance.index')->with('success', 'Attendance saved.');
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        if (! $request->user()->canManageAttendanceForClass((int) $request->input('school_class_id'))) {
            abort(403);
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('school_class_id', $request->school_class_id)),
            ],
            'subject_id' => [
                'nullable',
                Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('school_class_id', $request->school_class_id)),
            ],
            'status' => ['required', 'in:present,absent,late'],
            'note' => ['nullable', 'string'],
        ]);

        // Normalize optional subject_id ('' => null).
        $validated['subject_id'] = $validated['subject_id'] ?: null;

        $attendance->update($validated);

        return redirect()->route('attendance.index')->with('success', 'Attendance record updated.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $attendance->delete();

        return redirect()->route('attendance.index')->with('success', 'Attendance record deleted.');
    }

    /**
     * @return Collection<int, int>
     */
    private function allowedSchoolClassIds(User $user): Collection
    {
        if (in_array($user->role, ['admin', 'user'], true)) {
            return SchoolClass::query()->pluck('id');
        }

        return $user->teachingClasses()->pluck('school_classes.id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, SchoolClass>
     */
    private function classListForFilters(User $user)
    {
        $query = SchoolClass::query()->with('courseType')->orderBy('class_name')->orderBy('class_time');

        if ($user->role === 'teacher') {
            $query->forTeacher($user);
        }

        return $query->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, SchoolClass>
     */
    private function classListForMarking(User $user)
    {
        return $this->classListForFilters($user);
    }
}
