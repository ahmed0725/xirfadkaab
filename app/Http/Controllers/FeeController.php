<?php

namespace App\Http\Controllers;

use App\Models\AdditionalFeeCharge;
use App\Models\Fee;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeeController extends Controller
{
    public function index(Request $request): View
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $classId = $request->input('school_class_id');
        $studentId = $request->input('student_id');
        $paymentStatus = $request->input('payment_status');

        $fees = Fee::with(['student.schoolClass.courseType'])
            ->where('fee_month', $month)
            ->where('fee_year', $year)
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($classId, fn ($query) => $query->whereHas('student', fn ($sub) => $sub->where('school_class_id', $classId)))
            ->when($paymentStatus === 'paid', fn ($query) => $query->where('balance', '<=', 0))
            ->when($paymentStatus === 'pending', fn ($query) => $query->where('balance', '>', 0))
            ->latest('date')
            ->paginate(20)
            ->withQueryString();

        $classes = SchoolClass::with('courseType')->with('students')->orderBy('class_name')->orderBy('class_time')->get();
        $students = Student::with('schoolClass.courseType')->orderBy('name')->get();
        $classSummaries = $this->buildClassSummaries($month, $year, $classes);
        $additionalCategory = $request->input('additional_category');

        $additionalCharges = AdditionalFeeCharge::query()
            ->with('student.schoolClass.courseType')
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($classId, fn ($query) => $query->whereHas('student', fn ($sub) => $sub->where('school_class_id', $classId)))
            ->when($additionalCategory, fn ($query) => $query->where('category', $additionalCategory))
            ->latest('date')
            ->paginate(15, ['*'], 'additional_page')
            ->withQueryString();

        $filters = compact('month', 'year', 'classId', 'studentId', 'paymentStatus', 'additionalCategory');

        return view('fees.index', compact('fees', 'students', 'classes', 'classSummaries', 'filters', 'additionalCharges'));
    }

    public function create(): View
    {
        $months = $this->months();

        return view('fees.create', compact('months'));
    }

    public function store(Request $request): RedirectResponse
    {
        $student = Student::with('schoolClass.courseType')->find($request->input('student_id'));
        if ($student?->isFree()) {
            return back()
                ->withErrors(['student_id' => 'This student is registered as a free student and does not require tuition payments.'])
                ->withInput();
        }

        if ($student && ! $request->boolean('custom_amount')) {
            $request->merge(['amount' => $student->expectedTuitionAmount()]);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'paid' => ['required', 'numeric', 'min:0', 'lte:amount'],
            'fee_year' => ['required', 'integer', 'between:2000,2100'],
            'fee_month' => ['required', 'integer', 'between:1,12'],
            'date' => ['required', 'date'],
            'student_id' => [
                'required',
                'exists:students,id',
                Rule::unique('fees')->where(
                    fn ($query) => $query
                        ->where('student_id', $request->student_id)
                        ->where('fee_year', $request->fee_year)
                        ->where('fee_month', $request->fee_month)
                ),
            ],
        ]);

        $validated['balance'] = max(0, $validated['amount'] - $validated['paid']);
        $validated['receipt_no'] = $this->generateReceiptNo();

        $fee = Fee::create($validated);

        return redirect()->route('fees.receipt', $fee)->with('success', 'Payment recorded successfully.');
    }

    public function edit(Fee $fee): View
    {
        $fee->load('student.schoolClass');
        $months = $this->months();
        $classDefault = $fee->student->expectedTuitionAmount();
        $useCustomAmount = abs((float) $fee->amount - $classDefault) > 0.009;
        $selectedLabel = $fee->student
            ? "{$fee->student->name} ({$fee->student->student_id}) — ".($fee->student->schoolClass?->display_name ?? '-')
            : null;

        return view('fees.edit', compact('fee', 'months', 'useCustomAmount', 'selectedLabel'));
    }

    public function update(Request $request, Fee $fee): RedirectResponse
    {
        $student = Student::with('schoolClass.courseType')->find($request->input('student_id'));
        if ($student?->isFree()) {
            return back()
                ->withErrors(['student_id' => 'This student is registered as a free student and does not require tuition payments.'])
                ->withInput();
        }

        if ($student && ! $request->boolean('custom_amount')) {
            $request->merge(['amount' => $student->expectedTuitionAmount()]);
        }

        $validated = $request->validate([
            'student_id' => [
                'required',
                'exists:students,id',
                Rule::unique('fees')
                    ->ignore($fee->id)
                    ->where(
                        fn ($query) => $query
                            ->where('student_id', $request->student_id)
                            ->where('fee_year', $request->fee_year)
                            ->where('fee_month', $request->fee_month)
                    ),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'paid' => ['required', 'numeric', 'min:0', 'lte:amount'],
            'fee_year' => ['required', 'integer', 'between:2000,2100'],
            'fee_month' => ['required', 'integer', 'between:1,12'],
            'date' => ['required', 'date'],
        ]);

        $validated['balance'] = max(0, $validated['amount'] - $validated['paid']);
        $fee->update($validated);

        return redirect()->route('fees.index')->with('success', 'Payment updated.');
    }

    public function destroy(Fee $fee): RedirectResponse
    {
        $fee->delete();

        return redirect()->route('fees.index')->with('success', 'Payment deleted.');
    }

    public function receipt(Fee $fee): View
    {
        $fee->load('student');

        return view('fees.receipt', compact('fee'));
    }

    private function generateReceiptNo(): string
    {
        do {
            $receiptNo = 'RCP-'.now()->format('YmdHis').'-'.random_int(100, 999);
        } while (Fee::where('receipt_no', $receiptNo)->exists());

        return $receiptNo;
    }

    private function buildClassSummaries(int $month, int $year, $classes)
    {
        return $classes->map(function (SchoolClass $class) use ($month, $year) {
            $studentIds = $class->students->pluck('id');
            $feesForClass = Fee::query()
                ->whereIn('student_id', $studentIds)
                ->where('fee_month', $month)
                ->where('fee_year', $year)
                ->get()
                ->keyBy('student_id');

            $paidStudents = collect();
            $pendingStudents = collect();
            $totalPaid = 0;
            $totalPending = 0;

            foreach ($class->students as $student) {
                if ($student->isFree()) {
                    continue;
                }

                $fee = $feesForClass->get($student->id);
                if ($fee && (float) $fee->balance <= 0) {
                    $paidStudents->push($student);
                    $totalPaid += (float) $fee->paid;
                } else {
                    $pendingStudents->push($student);
                    $classAmount = (float) $class->monthly_fee_amount;
                    $totalPending += $fee ? (float) $fee->balance : $classAmount;
                    if ($fee) {
                        $totalPaid += (float) $fee->paid;
                    }
                }
            }

            return [
                'class' => $class,
                'paid_students' => $paidStudents,
                'pending_students' => $pendingStudents,
                'total_paid' => $totalPaid,
                'total_pending' => $totalPending,
            ];
        });
    }

    private function months(): array
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }
}
