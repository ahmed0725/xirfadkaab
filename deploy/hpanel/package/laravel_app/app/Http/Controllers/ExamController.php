<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(Request $request): View
    {
        $query = Exam::query()->with(['schoolClass.courseType', 'subject'])->orderByDesc('exam_date');

        if ($request->filled('school_class_id')) {
            $query->where('school_class_id', $request->school_class_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('exam_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('exam_date', '<=', $request->to);
        }
        if ($request->filled('student_id')) {
            $student = Student::find($request->student_id);
            if ($student) {
                $query->where('school_class_id', $student->school_class_id);
            }
        }

        $exams = $query->paginate(15)->withQueryString();
        $classes = SchoolClass::with('courseType')->orderBy('class_name')->orderBy('class_time')->get();
        $subjects = Subject::with('schoolClass.courseType')->orderBy('subject_name')->get();
        $filters = $request->only(['school_class_id', 'subject_id', 'from', 'to', 'student_id']);

        return view('exams.index', compact('exams', 'classes', 'subjects', 'filters'));
    }

    public function create(): View
    {
        $this->authorizeStaff();

        $classes = SchoolClass::with('courseType')->orderBy('class_name')->orderBy('class_time')->get();
        $subjects = Subject::with('schoolClass.courseType')->orderBy('subject_name')->get();

        return view('exams.create', compact('classes', 'subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeStaff();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'exam_date' => ['required', 'date'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => [
                'nullable',
                Rule::exists('subjects', 'id')->where(
                    fn ($q) => $q->where('school_class_id', $request->school_class_id)
                ),
            ],
            'max_marks' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $exam = Exam::create($validated);

        return redirect()->route('exams.show', $exam)->with('success', 'Exam created. Enter results below.');
    }

    public function show(Exam $exam): View
    {
        $exam->load([
            'schoolClass.courseType',
            'schoolClass.students',
            'subject',
            'examResults',
        ]);

        $resultsByStudent = $exam->examResults->keyBy('student_id');
        $marksList = $exam->examResults->pluck('marks_obtained');
        $stats = [
            'count' => $marksList->count(),
            'average' => $marksList->isEmpty() ? null : round((float) $marksList->avg(), 2),
            'min' => $marksList->isEmpty() ? null : round((float) $marksList->min(), 2),
            'max' => $marksList->isEmpty() ? null : round((float) $marksList->max(), 2),
        ];

        return view('exams.show', compact('exam', 'resultsByStudent', 'stats'));
    }

    public function edit(Exam $exam): View
    {
        $this->authorizeStaff();

        $classes = SchoolClass::with('courseType')->orderBy('class_name')->orderBy('class_time')->get();
        $subjects = Subject::with('schoolClass.courseType')->orderBy('subject_name')->get();

        return view('exams.edit', compact('exam', 'classes', 'subjects'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeStaff();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'exam_date' => ['required', 'date'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => [
                'nullable',
                Rule::exists('subjects', 'id')->where(
                    fn ($q) => $q->where('school_class_id', $request->school_class_id)
                ),
            ],
            'max_marks' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $exam->update($validated);

        return redirect()->route('exams.show', $exam)->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $this->authorizeStaff();

        $exam->delete();

        return redirect()->route('exams.index')->with('success', 'Exam deleted.');
    }

    public function storeResults(Request $request, Exam $exam): RedirectResponse
    {
        $exam->load('schoolClass.students');
        $max = (float) $exam->max_marks;

        $request->validate([
            'marks' => ['nullable', 'array'],
            'marks.*' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', 'string', 'max:500'],
        ]);

        $marks = $request->input('marks', []);
        $remarks = $request->input('remarks', []);
        $saved = 0;

        foreach ($exam->schoolClass->students as $student) {
            $key = (string) $student->id;
            if (! array_key_exists($key, $marks) && ! array_key_exists($student->id, $marks)) {
                continue;
            }
            $raw = $marks[$key] ?? $marks[$student->id] ?? null;
            if ($raw === null || $raw === '') {
                continue;
            }
            $obtained = (float) $raw;
            if ($obtained > $max) {
                return back()->withErrors([
                    "marks.{$student->id}" => "Marks for {$student->name} cannot exceed {$max}.",
                ])->withInput();
            }

            $remark = $remarks[$key] ?? $remarks[$student->id] ?? null;

            ExamResult::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                ],
                [
                    'marks_obtained' => $obtained,
                    'grade' => Exam::gradeFromMarks($obtained, $max),
                    'remarks' => $remark,
                ]
            );
            $saved++;
        }

        if ($saved === 0) {
            return redirect()->route('exams.show', $exam)->with('success', 'No marks entered. Fill at least one marks field to save.');
        }

        return redirect()->route('exams.show', $exam)->with('success', 'Results saved.');
    }

    private function authorizeStaff(): void
    {
        if (! in_array(auth()->user()->role, ['admin', 'user'], true)) {
            abort(403);
        }
    }
}
