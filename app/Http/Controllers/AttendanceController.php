<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $attendances = Attendance::with(['student', 'schoolClass', 'subject'])
            ->when($request->date, fn ($query, $date) => $query->whereDate('date', $date))
            ->when($request->school_class_id, fn ($query, $classId) => $query->where('school_class_id', $classId))
            ->when($request->student_id, fn ($query, $studentId) => $query->where('student_id', $studentId))
            ->latest('date')
            ->paginate(20)
            ->withQueryString();

        $classes = SchoolClass::with('students')->orderBy('class_name')->get();

        return view('attendance.index', compact('attendances', 'classes'));
    }

    public function create(): View
    {
        $classes = SchoolClass::with(['students', 'subjects'])->orderBy('class_name')->get();

        return view('attendance.mark', compact('classes'));
    }

    public function edit(Attendance $attendance): View
    {
        $classes = SchoolClass::with(['students', 'subjects'])->orderBy('class_name')->get();

        return view('attendance.edit', compact('attendance', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'exists:students,id'],
            'records.*.status' => ['required', 'in:present,absent,late'],
            'records.*.note' => ['nullable', 'string'],
        ]);

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

        $attendance->update($validated);

        return redirect()->route('attendance.index')->with('success', 'Attendance record updated.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $attendance->delete();

        return redirect()->route('attendance.index')->with('success', 'Attendance record deleted.');
    }
}
