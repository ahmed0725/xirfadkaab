<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Expense;
use App\Models\Fee;
use App\Models\InventoryItem;
use App\Models\SchoolClass;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
        'expenses_by_period' => 'Expenses by Period',
        'expenses_by_category' => 'Expenses by Category Summary',
        'inventory_available' => 'Inventory — Available Items',
        'inventory_low_stock' => 'Inventory — Low Stock Items',
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

        return $pdf->download('report-'.$data['selectedReportType'].'-'.now()->format('YmdHis').'.pdf');
    }

    private function buildReportData(Request $request): array
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $classId = $request->input('school_class_id');
        $studentId = $request->input('student_id');
        $expenseCategory = $request->input('expense_category');
        $selectedReportType = $request->input('report_type', 'monthly_fee_collection');
        if (! array_key_exists($selectedReportType, self::REPORT_TYPES)) {
            $selectedReportType = 'monthly_fee_collection';
        }

        $students = Student::query()
            ->with('schoolClass.courseType')
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->when($studentId, fn ($query) => $query->whereKey($studentId))
            ->orderBy('name')
            ->get();

        $fees = Fee::query()
            ->with(['student.schoolClass.courseType'])
            ->where('fee_month', $month)
            ->where('fee_year', $year)
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($classId, fn ($query) => $query->whereHas('student', fn ($sub) => $sub->where('school_class_id', $classId)))
            ->get();

        $attendance = Attendance::query()
            ->with(['student.schoolClass.courseType', 'schoolClass.courseType'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->get();

        $classes = SchoolClass::query()
            ->with(['students', 'courseType'])
            ->orderBy('class_name')
            ->orderBy('class_time')
            ->get();

        $exams = Exam::query()
            ->with(['schoolClass.courseType', 'subject', 'examResults'])
            ->whereMonth('exam_date', $month)
            ->whereYear('exam_date', $year)
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->orderByDesc('exam_date')
            ->get();

        $examResults = ExamResult::query()
            ->with(['student', 'exam.schoolClass.courseType', 'exam.subject'])
            ->whereHas('exam', function ($query) use ($month, $year) {
                $query->whereMonth('exam_date', $month)
                    ->whereYear('exam_date', $year);
            })
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($classId, fn ($query) => $query->whereHas('student', fn ($sub) => $sub->where('school_class_id', $classId)))
            ->orderByDesc('id')
            ->get();

        $expenses = Expense::query()
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->when($expenseCategory, fn ($query) => $query->where('category', $expenseCategory))
            ->orderByDesc('expense_date')
            ->get();

        $inventoryItems = InventoryItem::query()->orderBy('item_name')->get();

        $reportPayload = $this->generateReportPayload($selectedReportType, $students, $fees, $attendance, $classes, $month, $year, $exams, $examResults, $expenses, $inventoryItems);
        $filters = compact('month', 'year', 'classId', 'studentId', 'expenseCategory');

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

    private function generateReportPayload(string $reportType, Collection $students, Collection $fees, Collection $attendance, Collection $classes, int $month, int $year, Collection $exams, Collection $examResults, Collection $expenses, Collection $inventoryItems): array
    {
        $feesByStudent = $fees->keyBy('student_id');

        return match ($reportType) {
            'student_register' => [
                'table' => [
                    'columns' => ['Student ID', 'Name', 'Class', 'Status'],
                    'rows' => $students->map(fn ($s) => [$s->student_id, $s->name, $s->schoolClass?->display_name ?? '-', ucfirst($s->status)])->values()->all(),
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

                        return [$class->display_name, $classAttendance->where('status', 'present')->count(), $classAttendance->where('status', 'absent')->count(), $classAttendance->where('status', 'late')->count(), $classAttendance->count()];
                    })->values()->all(),
                ],
                'summary' => ['Classes' => $classes->count()],
            ],
            'attendance_by_student' => [
                'table' => [
                    'columns' => ['Student', 'Class', 'Present', 'Absent', 'Late'],
                    'rows' => $students->map(function ($student) use ($attendance) {
                        $sa = $attendance->where('student_id', $student->id);

                        return [$student->name, $student->schoolClass?->display_name ?? '-', $sa->where('status', 'present')->count(), $sa->where('status', 'absent')->count(), $sa->where('status', 'late')->count()];
                    })->values()->all(),
                ],
                'summary' => ['Students With Attendance' => $students->count()],
            ],
            'monthly_fee_collection' => [
                'table' => [
                    'columns' => ['Student', 'Class', 'Amount', 'Paid', 'Pending'],
                    'rows' => $fees->map(fn ($f) => [$f->student?->name ?? '-', $f->student?->schoolClass?->display_name ?? '-', number_format((float) $f->amount, 2), number_format((float) $f->paid, 2), number_format((float) $f->balance, 2)])->values()->all(),
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

                        return [$class->display_name, $paidCount, $pendingCount, number_format($totalPaid, 2), number_format($totalPending, 2)];
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

                        return [$student->name, $student->schoolClass?->display_name ?? '-', number_format($expected, 2), number_format($paid, 2), number_format($pending, 2)];
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
                            $exam->schoolClass?->display_name ?? '—',
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
                            $exam?->schoolClass?->display_name ?? '—',
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
            'expenses_by_period' => [
                'table' => [
                    'columns' => ['Date', 'Category', 'Amount', 'Payment method', 'Staff', 'Status', 'Description'],
                    'rows' => $expenses->map(function (Expense $e) {
                        return [
                            $e->expense_date->format('Y-m-d'),
                            Expense::CATEGORIES[$e->category] ?? $e->category,
                            number_format((float) $e->amount, 2),
                            Expense::PAYMENT_METHODS[$e->payment_method] ?? $e->payment_method,
                            $e->staff_name ?? '—',
                            Expense::STATUSES[$e->status] ?? $e->status,
                            Str::limit((string) $e->description, 60) ?: '—',
                        ];
                    })->values()->all(),
                ],
                'summary' => [
                    'Period' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
                    'Total' => number_format((float) $expenses->sum('amount'), 2),
                    'Count' => $expenses->count(),
                ],
            ],
            'expenses_by_category' => [
                'table' => [
                    'columns' => ['Category', 'Total amount', 'Transactions'],
                    'rows' => $expenses->groupBy('category')->map(function ($group, $cat) {
                        return [
                            Expense::CATEGORIES[$cat] ?? $cat,
                            number_format((float) $group->sum('amount'), 2),
                            (string) $group->count(),
                        ];
                    })->values()->all(),
                ],
                'summary' => [
                    'Period' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
                    'Grand total' => number_format((float) $expenses->sum('amount'), 2),
                ],
            ],
            'inventory_available' => [
                'table' => [
                    'columns' => ['Item', 'Category', 'Quantity', 'Condition', 'Purchase date', 'Notes'],
                    'rows' => $inventoryItems->filter(fn (InventoryItem $i) => $i->quantity > 0)->map(function (InventoryItem $i) {
                        return [
                            $i->item_name,
                            $i->category,
                            (string) $i->quantity,
                            InventoryItem::CONDITIONS[$i->condition] ?? $i->condition,
                            $i->purchase_date?->format('Y-m-d') ?? '—',
                            Str::limit((string) $i->notes, 40) ?: '—',
                        ];
                    })->values()->all(),
                ],
                'summary' => ['Items in stock' => $inventoryItems->filter(fn (InventoryItem $i) => $i->quantity > 0)->count()],
            ],
            'inventory_low_stock' => [
                'table' => [
                    'columns' => ['Item', 'Category', 'Quantity', 'Threshold', 'Condition'],
                    'rows' => $inventoryItems->filter(fn (InventoryItem $i) => $i->isLowStock())->map(function (InventoryItem $i) {
                        return [
                            $i->item_name,
                            $i->category,
                            (string) $i->quantity,
                            (string) $i->effectiveLowStockThreshold(),
                            InventoryItem::CONDITIONS[$i->condition] ?? $i->condition,
                        ];
                    })->values()->all(),
                ],
                'summary' => ['Low stock items' => $inventoryItems->filter(fn (InventoryItem $i) => $i->isLowStock())->count()],
            ],
            default => ['table' => ['columns' => [], 'rows' => []], 'summary' => []],
        };
    }
}
