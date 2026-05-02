<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Fee;
use App\Models\SchoolClass;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    private const REPORT_TYPES = [
        'student_register' => 'Student Register Report',
        'student_status' => 'Student Active/Inactive Report',
        'attendance_summary' => 'Attendance Summary Report',
        'attendance_by_class' => 'Attendance by Class Report',
        'attendance_by_student' => 'Attendance by Student Report',
        'monthly_fee_collection' => 'Monthly Fee Collection Report',
        'paid_vs_unpaid' => 'Paid vs Unpaid Students Report',
        'class_fee_summary' => 'Class Fee Summary Report',
        'pending_fees' => 'Pending Fees Report',
        'revenue_trend' => 'Fee Revenue Trend Report',
        'exam_results_by_class' => 'Exam Results by Class',
        'student_exam_history' => 'Student Exam History',
    ];

    public function index(Request $request): View
    {
        $data = $this->buildReportData($request);

        return view('reports.index', $data);
    }

    public function print(Request $request): View
    {
        return view('reports.print', $this->buildReportData($request));
    }

    public function pdf(Request $request)
    {
        $data = $this->buildReportData($request);
        $pdf = Pdf::loadView('reports.print', $data);

        return $pdf->download('report-' . $data['selectedReportType'] . '-' . now()->format('YmdHis') . '.pdf');
    }

    private function buildReportData(Request $request): array
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $classId = $request->input('school_class_id');
        $studentId = $request->input('student_id');
        $selectedReportType = $request->input('report_type', 'monthly_fee_collection');
        if (! array_key_exists($selectedReportType, self::REPORT_TYPES)) {
            $selectedReportType = 'monthly_fee_collection';
        }

        $students = Student::query()
            ->with('schoolClass')
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->when($studentId, fn ($query) => $query->whereKey($studentId))
            ->orderBy('name')
            ->get();

        $fees = Fee::query()
            ->with(['student.schoolClass'])
            ->where('fee_month', $month)
            ->where('fee_year', $year)
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($classId, fn ($query) => $query->whereHas('student', fn ($sub) => $sub->where('school_class_id', $classId)))
            ->get();

        $attendance = Attendance::query()
            ->with(['student.schoolClass', 'schoolClass'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->get();

        $classes = SchoolClass::query()
            ->with('students')
            ->orderBy('class_name')
            ->get();

        $exams = Exam::query()
            ->with(['schoolClass', 'subject', 'examResults'])
            ->whereMonth('exam_date', $month)
            ->whereYear('exam_date', $year)
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->orderByDesc('exam_date')
            ->get();

        $examResults = ExamResult::query()
            ->with(['student', 'exam.schoolClass', 'exam.subject'])
            ->whereHas('exam', function ($query) use ($month, $year) {
                $query->whereMonth('exam_date', $month)
                    ->whereYear('exam_date', $year);
            })
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($classId, fn ($query) => $query->whereHas('student', fn ($sub) => $sub->where('school_class_id', $classId)))
            ->orderByDesc('id')
            ->get();

        $reportPayload = $this->generateReportPayload($selectedReportType, $students, $fees, $attendance, $classes, $month, $year, $exams, $examResults);
        $filters = compact('month', 'year', 'classId', 'studentId');

        return [
            'reportTypes' => self::REPORT_TYPES,
            'selectedReportType' => $selectedReportType,
            'selectedReportLabel' => self::REPORT_TYPES[$selectedReportType],
            'classes' => $classes,
            'students' => Student::orderBy('name')->get(),
            'filters' => $filters,
            'periodLabel' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
            'reportTable' => $reportPayload['table'],
            'reportSummary' => $reportPayload['summary'],
        ];
    }

    private function generateReportPayload(string $reportType, Collection $students, Collection $fees, Collection $attendance, Collection $classes, int $month, int $year, Collection $exams, Collection $examResults): array
    {
        $feesByStudent = $fees->keyBy('student_id');

        return match ($reportType) {
            'student_register' => [
                'table' => [
                    'columns' => ['Student ID', 'Name', 'Class', 'Status'],
                    'rows' => $students->map(fn ($s) => [$s->student_id, $s->name, $s->schoolClass?->class_name ?? '-', ucfirst($s->status)])->values()->all(),
                ],
                'summary' => ['Total Students' => $students->count()],
            ],
            'student_status' => [
                'table' => [
                    'columns' => ['Status', 'Count'],
                    'rows' => collect(['active', 'inactive'])->map(fn ($status) => [ucfirst($status), $students->where('status', $status)->count()])->all(),
                ],
                'summary' => ['Active' => $students->where('status', 'active')->count(), 'Inactive' => $students->where('status', 'inactive')->count()],
            ],
            'attendance_summary' => [
                'table' => [
                    'columns' => ['Attendance Status', 'Count'],
                    'rows' => collect(['present', 'absent', 'late'])->map(fn ($status) => [ucfirst($status), $attendance->where('status', $status)->count()])->all(),
                ],
                'summary' => ['Total Records' => $attendance->count()],
            ],
            'attendance_by_class' => [
                'table' => [
                    'columns' => ['Class', 'Present', 'Absent', 'Late', 'Total'],
                    'rows' => $classes->map(function ($class) use ($attendance) {
                        $classAttendance = $attendance->where('school_class_id', $class->id);
                        return [$class->class_name, $classAttendance->where('status', 'present')->count(), $classAttendance->where('status', 'absent')->count(), $classAttendance->where('status', 'late')->count(), $classAttendance->count()];
                    })->values()->all(),
                ],
                'summary' => ['Classes' => $classes->count()],
            ],
            'attendance_by_student' => [
                'table' => [
                    'columns' => ['Student', 'Class', 'Present', 'Absent', 'Late'],
                    'rows' => $students->map(function ($student) use ($attendance) {
                        $sa = $attendance->where('student_id', $student->id);
                        return [$student->name, $student->schoolClass?->class_name ?? '-', $sa->where('status', 'present')->count(), $sa->where('status', 'absent')->count(), $sa->where('status', 'late')->count()];
                    })->values()->all(),
                ],
                'summary' => ['Students With Attendance' => $students->count()],
            ],
            'monthly_fee_collection' => [
                'table' => [
                    'columns' => ['Student', 'Class', 'Amount', 'Paid', 'Pending'],
                    'rows' => $fees->map(fn ($f) => [$f->student?->name ?? '-', $f->student?->schoolClass?->class_name ?? '-', number_format((float) $f->amount, 2), number_format((float) $f->paid, 2), number_format((float) $f->balance, 2)])->values()->all(),
                ],
                'summary' => ['Period' => Carbon::createFromDate($year, $month, 1)->format('F Y'), 'Total Paid' => number_format((float) $fees->sum('paid'), 2), 'Total Pending' => number_format((float) $fees->sum('balance'), 2)],
            ],
            'paid_vs_unpaid' => [
                'table' => [
                    'columns' => ['Category', 'Count'],
                    'rows' => [
                        ['Paid Students', $students->filter(fn ($s) => (($feesByStudent[$s->id]->balance ?? 1) <= 0))->count()],
                        ['Unpaid Students', $students->filter(fn ($s) => ! isset($feesByStudent[$s->id]) || (($feesByStudent[$s->id]->balance ?? 0) > 0))->count()],
                    ],
                ],
                'summary' => ['Total Students' => $students->count()],
            ],
            'class_fee_summary' => [
                'table' => [
                    'columns' => ['Class', 'Paid Students', 'Pending Students', 'Total Paid', 'Total Pending'],
                    'rows' => $classes->map(function ($class) use ($feesByStudent) {
                        $paidCount = 0;
                        $pendingCount = 0;
                        $totalPaid = 0;
                        $totalPending = 0;
                        foreach ($class->students as $student) {
                            $fee = $feesByStudent->get($student->id);
                            if ($fee && (float) $fee->balance <= 0) {
                                $paidCount++;
                                $totalPaid += (float) $fee->paid;
                            } else {
                                $pendingCount++;
                                $totalPending += $fee ? (float) $fee->balance : (float) $class->monthly_fee_amount;
                                if ($fee) {
                                    $totalPaid += (float) $fee->paid;
                                }
                            }
                        }
                        return [$class->class_name, $paidCount, $pendingCount, number_format($totalPaid, 2), number_format($totalPending, 2)];
                    })->values()->all(),
                ],
                'summary' => ['Classes' => $classes->count()],
            ],
            'pending_fees' => [
                'table' => [
                    'columns' => ['Student', 'Class', 'Expected', 'Paid', 'Pending'],
                    'rows' => $students->map(function ($student) use ($feesByStudent) {
                        $fee = $feesByStudent->get($student->id);
                        $expected = $fee ? (float) $fee->amount : (float) ($student->schoolClass?->monthly_fee_amount ?? 0);
                        $paid = $fee ? (float) $fee->paid : 0;
                        $pending = $fee ? (float) $fee->balance : $expected;
                        return [$student->name, $student->schoolClass?->class_name ?? '-', number_format($expected, 2), number_format($paid, 2), number_format($pending, 2)];
                    })->filter(fn ($row) => (float) str_replace(',', '', $row[4]) > 0)->values()->all(),
                ],
                'summary' => ['Total Pending Amount' => number_format((float) $students->sum(function ($student) use ($feesByStudent) {
                    $fee = $feesByStudent->get($student->id);
                    return $fee ? (float) $fee->balance : (float) ($student->schoolClass?->monthly_fee_amount ?? 0);
                }), 2)],
            ],
            'revenue_trend' => [
                'table' => [
                    'columns' => ['Month', 'Collected'],
                    'rows' => collect(range(1, 12))->map(function ($m) use ($fees, $year) {
                        return [Carbon::createFromDate($year, $m, 1)->format('F'), number_format((float) $fees->where('fee_month', $m)->sum('paid'), 2)];
                    })->all(),
                ],
                'summary' => ['Year Total Collection' => number_format((float) $fees->sum('paid'), 2)],
            ],
            'exam_results_by_class' => [
                'table' => [
                    'columns' => ['Exam', 'Class', 'Subject', 'Date', 'Max', 'Average', 'Results recorded'],
                    'rows' => $exams->map(function (Exam $exam) {
                        $results = $exam->examResults;
                        $avg = $results->isEmpty() ? '—' : number_format((float) $results->avg('marks_obtained'), 2);

                        return [
                            $exam->title,
                            $exam->schoolClass?->class_name ?? '—',
                            $exam->subject?->subject_name ?? '—',
                            $exam->exam_date->format('Y-m-d'),
                            number_format((float) $exam->max_marks, 2),
                            $avg,
                            (string) $results->count(),
                        ];
                    })->values()->all(),
                ],
                'summary' => ['Exams in period' => $exams->count()],
            ],
            'student_exam_history' => [
                'table' => [
                    'columns' => ['Student', 'Exam', 'Class', 'Subject', 'Marks', 'Grade', 'Date'],
                    'rows' => $examResults->map(function (ExamResult $row) {
                        $exam = $row->exam;

                        return [
                            $row->student?->name ?? '—',
                            $exam?->title ?? '—',
                            $exam?->schoolClass?->class_name ?? '—',
                            $exam?->subject?->subject_name ?? '—',
                            number_format((float) $row->marks_obtained, 2),
                            $row->grade ?? '—',
                            $exam?->exam_date?->format('Y-m-d') ?? '—',
                        ];
                    })->values()->all(),
                ],
                'summary' => array_merge(
                    ['Rows' => $examResults->count()],
                    $examResults->isEmpty() ? ['Hint' => 'Select a student or class filter if the list is empty.'] : []
                ),
            ],
            default => ['table' => ['columns' => [], 'rows' => []], 'summary' => []],
        };
    }
}
