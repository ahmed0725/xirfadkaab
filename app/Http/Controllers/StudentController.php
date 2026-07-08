<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $students = Student::query()
            ->with(['schoolClass.courseType'])
            ->when(request('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%")
                ->orWhere('student_id', 'like', "%{$search}%"))
            ->when(request('school_class_id'), fn ($query, $classId) => $query->where('school_class_id', $classId))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $classes = SchoolClass::with('courseType')->orderBy('class_name')->orderBy('class_time')->get();

        return view('students.index', compact('students', 'classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $classes = SchoolClass::query()->with('courseType')->active()->orderBy('class_name')->orderBy('class_time')->get();

        return view('students.create', compact('classes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['student_id'] = Student::generateNextStudentId();

        Student::create($data);

        return redirect()->route('students.index')->with('success', 'Student registered successfully.');
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        $students = Student::query()
            ->with('schoolClass.courseType')
            ->when($request->boolean('tuition_only'), fn ($builder) => $builder->requiresTuition())
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%")
                        ->orWhere('student_id', 'like', "%{$query}%");
                });
            })
            ->orderBy('name')
            ->limit(25)
            ->get()
            ->map(fn (Student $student) => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'name' => $student->name,
                'class_name' => $student->schoolClass?->display_name ?? '-',
                'monthly_fee' => $student->expectedTuitionAmount(),
                'fee_type' => $student->fee_type,
            ]);

        return response()->json($students);
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): View
    {
        $student->load([
            'schoolClass.courseType',
            'attendances',
            'fees',
            'additionalFeeCharges' => fn ($q) => $q->latest('date')->limit(20),
        ]);

        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student): View
    {
        $classes = SchoolClass::query()
            ->with('courseType')
            ->where(function ($query) use ($student) {
                $query->where('is_active', true)
                    ->orWhere('id', $student->school_class_id);
            })
            ->orderBy('class_name')
            ->orderBy('class_time')
            ->get();

        return view('students.edit', compact('student', 'classes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->validated());

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student deleted.');
    }
}
