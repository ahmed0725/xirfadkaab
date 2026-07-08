<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Fee;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();
        $startMonth = now()->copy()->startOfMonth();
        $endMonth = now()->copy()->endOfMonth();

        $expensesThisMonth = (float) Expense::query()
            ->whereBetween('expense_date', [$startMonth->toDateString(), $endMonth->toDateString()])
            ->sum('amount');

        $payrollPending = Expense::query()
            ->where('category', Expense::CATEGORY_PAYROLL)
            ->where('status', 'unpaid')
            ->count();

        $stats = [
            'students' => Student::count(),
            'active_students' => Student::where('status', 'active')->count(),
            'inactive_students' => Student::where('status', 'inactive')->count(),
            'classes' => SchoolClass::count(),
            'active_classes' => SchoolClass::where('is_active', true)->count(),
            'inactive_classes' => SchoolClass::where('is_active', false)->count(),
            'regular_students' => Student::where('fee_type', Student::FEE_TYPE_REGULAR)->count(),
            'free_students' => Student::where('fee_type', Student::FEE_TYPE_FREE)->count(),
            'fees_collected' => Fee::sum('paid'),
            'pending_fees' => Fee::sum('balance'),
            'present_today' => Attendance::whereDate('date', $today)->where('status', 'present')->count(),
            'absent_today' => Attendance::whereDate('date', $today)->where('status', 'absent')->count(),
            'late_today' => Attendance::whereDate('date', $today)->where('status', 'late')->count(),
            'expenses_this_month' => $expensesThisMonth,
            'payroll_pending_count' => $payrollPending,
        ];

        $feeTrend = Fee::query()
            ->selectRaw('date, sum(paid) as total')
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit(7)
            ->get()
            ->reverse()
            ->values();

        $attendanceDistribution = Attendance::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $charts = [
            'fee_labels' => $feeTrend->pluck('date')->map(fn ($date) => (string) $date)->all(),
            'fee_values' => $feeTrend->pluck('total')->map(fn ($value) => (float) $value)->all(),
            'attendance_labels' => ['Present', 'Absent', 'Late'],
            'attendance_values' => [
                (int) ($attendanceDistribution['present'] ?? 0),
                (int) ($attendanceDistribution['absent'] ?? 0),
                (int) ($attendanceDistribution['late'] ?? 0),
            ],
        ];

        return view('dashboard', compact('stats', 'charts'));
    }
}
