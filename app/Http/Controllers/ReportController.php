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
    private const REPORT_GROUPS = [
        'Students' => [
            'student_register' => 'Student Register Report',
            'student_status' => 'Student Active/Inactive Report',
            'students_by_fee_type' => 'Free vs Regular Students Report',
        ],
        'Classes' => [
            'classes_status' => 'Active/Inactive Classes Report',
        ],
        'Attendance' => [
            'attendance_summary' => 'Attendance Summary Report',
            'attendance_by_class' => 'Attendance by Class Report',
            'attendance_by_student' => 'Attendance by Student Report',
        ],
        'Fees' => [
            'monthly_fee_collection' => 'Monthly Fee Collection Report',
            'paid_vs_unpaid' => 'Paid vs Unpaid Students Report',
            'class_fee_summary' => 'Class Fee Summary Report',
            'pending_fees' => 'Pending Fees Report',
            'revenue_trend' => 'Fee Revenue Trend Report',
        ],
        'Exams' => [
            'exam_results_by_class' => 'Exam Results by Class',
            'student_exam_history' => 'Student Exam History',
        ],
        'Expenses' => [
            'expenses_by_period' => 'Expenses by Period',
            'expenses_by_category' => 'Expenses by Category Summary',
            'expenses_by_staff' => 'Expenses by Individual (Staff)',
        ],
        'Inventory' => [
            'inventory_available' => 'Inventory — Available Items',
            'inventory_low_stock' => 'Inventory — Low Stock Items',
        ],
    ];

    /**
     * Which filters are relevant per report type (drives the filter UI).
     */
    private const REPORT_FILTERS = [
        'student_register' => ['class', 'student', 'status', 'fee_type'],
        'student_status' => ['class', 'status'],
        'students_by_fee_type' => ['class', 'fee_type'],
        'classes_status' => ['class_status'],
        'attendance_summary' => ['date_range', 'class', 'student'],
        'attendance_by_class' => ['date_range', 'class'],
        'attendance_by_student' => ['date_range', 'class', 'student'],
        'monthly_fee_collection' => ['month', 'year', 'class', 'student'],
        'paid_vs_unpaid' => ['month', 'year', 'class'],
        'class_fee_summary' => ['month', 'year', 'class'],
        'pending_fees' => ['month', 'year', 'class', 'student'],
        'revenue_trend' => ['year', 'class', 'student'],
        'exam_results_by_class' => ['date_range', 'class'],
        'student_exam_history' => ['date_range', 'class', 'student'],
        'expenses_by_period' => ['date_range', 'expense_category'],
        'expenses_by_category' => ['date_range', 'expense_category'],
        'expenses_by_staff' => ['date_range', 'expense_category'],
        'inventory_available' => [],
        'inventory_low_stock' => [],
    ];

    private const GROUP_DESCRIPTIONS = [
        'Students' => 'Enrollment register, active/inactive status, and free vs regular breakdowns.',
        'Classes' => 'Class list with schedules, enrollment counts, and active/inactive status.',
        'Attendance' => 'Attendance totals and breakdowns by class or by student.',
        'Fees' => 'Tuition collection, paid vs unpaid, pending balances, and revenue trends.',
        'Exams' => 'Exam results by class and per-student exam history.',
        'Expenses' => 'Spending by period, by category, and by individual staff member.',
        'Inventory' => 'Available stock and low-stock alerts.',
    ];

    private static function reportTypes(): array
    {
        return array_merge(...array_values(self::REPORT_GROUPS));
    }

    public function index(Request $request): View
    {
        // No report selected yet: show the hub with one card per module.
        if (! $request->filled('report_type')) {
            return view('reports.hub', [
                'reportGroups' => self::REPORT_GROUPS,
                'groupDescriptions' => self::GROUP_DESCRIPTIONS,
                'overviewStats' => $this->overviewStats(),
            ]);
        }

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
        $reportTypes = self::reportTypes();
        $selectedReportType = $request->input('report_type', 'monthly_fee_collection');
        if (! array_key_exists($selectedReportType, $reportTypes)) {
            $selectedReportType = 'monthly_fee_collection';
        }

        $activeFilters = self::REPORT_FILTERS[$selectedReportType] ?? [];

        // Only honor a filter when the selected report actually offers it, so
        // stale query params can't invisibly narrow an unrelated report.
        $statusFilter = in_array('status', $activeFilters, true) ? $request->input('status') : null;
        if (! in_array($statusFilter, ['active', 'inactive'], true)) {
            $statusFilter = null;
        }
        $feeTypeFilter = in_array('fee_type', $activeFilters, true) ? $request->input('fee_type') : null;
        if (! in_array($feeTypeFilter, [Student::FEE_TYPE_REGULAR, Student::FEE_TYPE_FREE], true)) {
            $feeTypeFilter = null;
        }
        $classStatusFilter = in_array('class_status', $activeFilters, true) ? $request->input('class_status') : null;
        if (! in_array($classStatusFilter, ['active', 'inactive'], true)) {
            $classStatusFilter = null;
        }

        // Date-range reports (attendance, exams, expenses) filter by an
        // explicit from/to date; fee reports keep a monthly fee period.
        $fromDate = $this->parseDate($request->input('from_date')) ?? now()->startOfMonth();
        $toDate = $this->parseDate($request->input('to_date')) ?? now()->endOfMonth();
        if ($toDate->lt($fromDate)) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }
        $fromDate = $fromDate->startOfDay();
        $toDate = $toDate->endOfDay();

        $periodLabel = match (true) {
            in_array('date_range', $activeFilters, true) => $fromDate->format('M j, Y').' — '.$toDate->format('M j, Y'),
            in_array('month', $activeFilters, true) || in_array('year', $activeFilters, true) => Carbon::createFromDate($year, $month, 1)->format('F Y'),
            default => 'As of '.now()->format('M j, Y'),
        };

        $students = Student::query()
            ->with('schoolClass.courseType')
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->when($studentId, fn ($query) => $query->whereKey($studentId))
            ->when($statusFilter, fn ($query) => $query->where('status', $statusFilter))
            ->when($feeTypeFilter, fn ($query) => $query->where('fee_type', $feeTypeFilter))
            ->orderBy('name')
            ->get();

        $fees = Fee::query()
            ->with(['student.schoolClass.courseType'])
            // The revenue trend report charts the whole year; all others are month-scoped.
            ->when($selectedReportType !== 'revenue_trend', fn ($query) => $query->where('fee_month', $month))
            ->where('fee_year', $year)
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($classId, fn ($query) => $query->whereHas('student', fn ($sub) => $sub->where('school_class_id', $classId)))
            ->get();

        $attendance = Attendance::query()
            ->with(['student.schoolClass.courseType', 'schoolClass.courseType'])
            ->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->get();

        $classes = SchoolClass::query()
            ->with(['students', 'courseType'])
            ->when($classStatusFilter, fn ($query) => $query->where('is_active', $classStatusFilter === 'active'))
            ->orderBy('class_name')
            ->orderBy('class_time')
            ->get();

        $exams = Exam::query()
            ->with(['schoolClass.courseType', 'subject', 'examResults'])
            ->whereBetween('exam_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->orderByDesc('exam_date')
            ->get();

        $examResults = ExamResult::query()
            ->with(['student', 'exam.schoolClass.courseType', 'exam.subject'])
            ->whereHas('exam', function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('exam_date', [$fromDate->toDateString(), $toDate->toDateString()]);
            })
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($classId, fn ($query) => $query->whereHas('student', fn ($sub) => $sub->where('school_class_id', $classId)))
            ->orderByDesc('id')
            ->get();

        $expenses = Expense::query()
            ->whereBetween('expense_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->when($expenseCategory, fn ($query) => $query->where('category', $expenseCategory))
            ->orderByDesc('expense_date')
            ->get();

        $inventoryItems = InventoryItem::query()->orderBy('item_name')->get();

        $reportPayload = $this->generateReportPayload($selectedReportType, $students, $fees, $attendance, $classes, $month, $year, $exams, $examResults, $expenses, $inventoryItems, $periodLabel);
        $filters = array_merge(compact('month', 'year', 'classId', 'studentId', 'expenseCategory'), [
            'fromDate' => $fromDate->toDateString(),
            'toDate' => $toDate->toDateString(),
            'status' => $statusFilter,
            'feeType' => $feeTypeFilter,
            'classStatus' => $classStatusFilter,
        ]);

        $currentGroup = 'Fees';
        foreach (self::REPORT_GROUPS as $group => $types) {
            if (array_key_exists($selectedReportType, $types)) {
                $currentGroup = $group;
                break;
            }
        }

        return [
            'currentGroup' => $currentGroup,
            'groupReports' => self::REPORT_GROUPS[$currentGroup],
            'activeFilters' => $activeFilters,
            'selectedReportType' => $selectedReportType,
            'selectedReportLabel' => $reportTypes[$selectedReportType],
            'classes' => $classes,
            'students' => Student::orderBy('name')->get(),
            'filters' => $filters,
            'periodLabel' => $periodLabel,
            'reportTable' => $reportPayload['table'],
            'reportSummary' => $reportPayload['summary'],
        ];
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Hub tiles: each count links to the report listing exactly those records.
     */
    private function overviewStats(): array
    {
        return [
            'Active Classes' => [
                'value' => SchoolClass::where('is_active', true)->count(),
                'url' => route('reports.index', ['report_type' => 'classes_status', 'class_status' => 'active']),
            ],
            'Inactive Classes' => [
                'value' => SchoolClass::where('is_active', false)->count(),
                'url' => route('reports.index', ['report_type' => 'classes_status', 'class_status' => 'inactive']),
            ],
            'Active Students' => [
                'value' => Student::where('status', 'active')->count(),
                'url' => route('reports.index', ['report_type' => 'student_status', 'status' => 'active']),
            ],
            'Inactive Students' => [
                'value' => Student::where('status', 'inactive')->count(),
                'url' => route('reports.index', ['report_type' => 'student_status', 'status' => 'inactive']),
            ],
            'Regular Students' => [
                'value' => Student::where('fee_type', Student::FEE_TYPE_REGULAR)->count(),
                'url' => route('reports.index', ['report_type' => 'students_by_fee_type', 'fee_type' => Student::FEE_TYPE_REGULAR]),
            ],
            'Free Students' => [
                'value' => Student::where('fee_type', Student::FEE_TYPE_FREE)->count(),
                'url' => route('reports.index', ['report_type' => 'students_by_fee_type', 'fee_type' => Student::FEE_TYPE_FREE]),
            ],
        ];
    }

    private function generateReportPayload(string $reportType, Collection $students, Collection $fees, Collection $attendance, Collection $classes, int $month, int $year, Collection $exams, Collection $examResults, Collection $expenses, Collection $inventoryItems, string $periodLabel): array
    {
        $feesByStudent = $fees->keyBy('student_id');

        return match ($reportType) {
            'student_register' => [
                'table' => [
                    'columns' => ['Student ID', 'Name', 'Class', 'Status', 'Fee Status'],
                    'rows' => $students->map(fn ($s) => [
                        $s->student_id,
                        $s->name,
                        $s->schoolClass?->display_name ?? '-',
                        ucfirst($s->status),
                        $s->isFree() ? 'Free (No Tuition)' : 'Regular (Tuition Required)',
                    ])->values()->all(),
                ],
                'summary' => [
                    'Total Students' => $students->count(),
                    'Regular Students' => $students->where('fee_type', Student::FEE_TYPE_REGULAR)->count(),
                    'Free Students' => $students->where('fee_type', Student::FEE_TYPE_FREE)->count(),
                ],
            ],
            'student_status' => [
                'table' => [
                    'columns' => ['Status', 'Student ID', 'Name', 'Class', 'Fee Type'],
                    'rows' => $students
                        ->sortBy(fn ($s) => [$s->status, $s->name])
                        ->map(fn ($s) => [
                            ucfirst($s->status),
                            $s->student_id,
                            $s->name,
                            $s->schoolClass?->display_name ?? '-',
                            $s->isFree() ? 'Free' : 'Regular',
                        ])->values()->all(),
                ],
                'summary' => [
                    'Active' => $students->where('status', 'active')->count(),
                    'Inactive' => $students->where('status', 'inactive')->count(),
                    'Students Listed' => $students->count(),
                ],
            ],
            'students_by_fee_type' => [
                'table' => [
                    'columns' => ['Fee Type', 'Student ID', 'Name', 'Class', 'Status'],
                    'rows' => $students
                        ->sortBy(fn ($s) => [$s->fee_type === Student::FEE_TYPE_FREE ? 0 : 1, $s->name])
                        ->map(fn ($s) => [
                            $s->isFree() ? 'Free (No Tuition)' : 'Regular (Tuition Required)',
                            $s->student_id,
                            $s->name,
                            $s->schoolClass?->display_name ?? '-',
                            ucfirst($s->status),
                        ])->values()->all(),
                ],
                'summary' => [
                    'Free Students' => $students->where('fee_type', Student::FEE_TYPE_FREE)->count(),
                    'Regular Students' => $students->where('fee_type', Student::FEE_TYPE_REGULAR)->count(),
                    'Total Students' => $students->count(),
                ],
            ],
            'classes_status' => [
                'table' => [
                    'columns' => ['Class', 'Shift', 'Time', 'Students', 'Start Date', 'End Date', 'Status'],
                    'rows' => $classes
                        ->sortBy(fn ($c) => [$c->is_active ? 0 : 1, $c->class_name])
                        ->map(fn ($c) => [
                            $c->display_name,
                            ucfirst((string) $c->shift) ?: '—',
                            $c->formattedClassTime() ?: '—',
                            (string) $c->students->count(),
                            $c->start_date?->format('Y-m-d') ?? '—',
                            $c->end_date?->format('Y-m-d') ?? '—',
                            $c->is_active ? 'Active' : 'Inactive',
                        ])->values()->all(),
                ],
                'summary' => [
                    'Active Classes' => $classes->where('is_active', true)->count(),
                    'Inactive Classes' => $classes->where('is_active', false)->count(),
                    'Total Classes' => $classes->count(),
                    'Students in Active Classes' => $classes->where('is_active', true)->sum(fn ($c) => $c->students->count()),
                    'Students in Inactive Classes' => $classes->where('is_active', false)->sum(fn ($c) => $c->students->count()),
                ],
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
                    'columns' => ['Student', 'Class', 'Fee Status', 'Amount', 'Paid', 'Pending'],
                    'rows' => $fees->map(fn ($f) => [
                        $f->student?->name ?? '-',
                        $f->student?->schoolClass?->display_name ?? '-',
                        $f->student?->isFree() ? 'Free' : 'Regular',
                        number_format((float) $f->amount, 2),
                        number_format((float) $f->paid, 2),
                        number_format((float) $f->balance, 2),
                    ])->values()->all(),
                ],
                'summary' => [
                    'Period' => $periodLabel,
                    'Total Paid' => number_format((float) $fees->sum('paid'), 2),
                    'Total Pending' => number_format((float) $fees->sum('balance'), 2),
                ],
            ],
            'paid_vs_unpaid' => [
                'table' => [
                    'columns' => ['Category', 'Count'],
                    'rows' => [
                        ['Paid Students', $students->filter(fn ($s) => $s->requiresTuition() && (($feesByStudent[$s->id]->balance ?? 1) <= 0))->count()],
                        ['Unpaid Students', $students->filter(fn ($s) => $s->requiresTuition() && (! isset($feesByStudent[$s->id]) || (($feesByStudent[$s->id]->balance ?? 0) > 0)))->count()],
                        ['Free Students (No Tuition)', $students->filter(fn ($s) => $s->isFree())->count()],
                    ],
                ],
                'summary' => [
                    'Tuition Students' => $students->where('fee_type', Student::FEE_TYPE_REGULAR)->count(),
                    'Free Students' => $students->where('fee_type', Student::FEE_TYPE_FREE)->count(),
                ],
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
                    'Period' => $periodLabel,
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
                    'Period' => $periodLabel,
                    'Grand total' => number_format((float) $expenses->sum('amount'), 2),
                ],
            ],
            'expenses_by_staff' => [
                'table' => [
                    'columns' => ['Individual / Staff', 'Transactions', 'Categories', 'Total Amount'],
                    'rows' => $expenses
                        ->groupBy(fn (Expense $e) => $e->staff_name ?: '— General (no individual)')
                        ->sortByDesc(fn ($group) => $group->sum('amount'))
                        ->map(fn ($group, $staff) => [
                            $staff,
                            (string) $group->count(),
                            $group->pluck('category')->unique()->map(fn ($c) => Expense::CATEGORIES[$c] ?? $c)->implode(', '),
                            number_format((float) $group->sum('amount'), 2),
                        ])->values()->all(),
                ],
                'summary' => [
                    'Period' => $periodLabel,
                    'Individuals' => $expenses->pluck('staff_name')->filter()->unique()->count(),
                    'Grand Total' => number_format((float) $expenses->sum('amount'), 2),
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
