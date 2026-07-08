<?php

namespace App\Http\Controllers;

use App\Models\AdditionalFeeCharge;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdditionalFeeController extends Controller
{
    public function create(): View
    {
        return view('additional-fees.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'category' => ['required', 'string', 'in:books,certificates,other'],
            'title' => ['nullable', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'paid' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
        ]);

        if ((float) $validated['paid'] > (float) $validated['total_amount']) {
            return back()->withErrors(['paid' => 'Paid amount cannot exceed total amount.'])->withInput();
        }

        $validated['balance'] = max(0, (float) $validated['total_amount'] - (float) $validated['paid']);
        $validated['receipt_no'] = $this->generateReceiptNo();

        $charge = AdditionalFeeCharge::create($validated);

        return redirect()->route('additional-fees.receipt', $charge)->with('success', 'Additional charge recorded.');
    }

    public function edit(AdditionalFeeCharge $additionalFeeCharge): View
    {
        $additionalFeeCharge->load('student.schoolClass');
        $selectedLabel = $additionalFeeCharge->student
            ? "{$additionalFeeCharge->student->name} ({$additionalFeeCharge->student->student_id}) — ".($additionalFeeCharge->student->schoolClass?->display_name ?? '-')
            : null;

        return view('additional-fees.edit', [
            'additionalFee' => $additionalFeeCharge,
            'selectedLabel' => $selectedLabel,
        ]);
    }

    public function update(Request $request, AdditionalFeeCharge $additionalFeeCharge): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'category' => ['required', 'string', 'in:books,certificates,other'],
            'title' => ['nullable', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'paid' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
        ]);

        if ((float) $validated['paid'] > (float) $validated['total_amount']) {
            return back()->withErrors(['paid' => 'Paid amount cannot exceed total amount.'])->withInput();
        }

        $validated['balance'] = max(0, (float) $validated['total_amount'] - (float) $validated['paid']);
        $additionalFeeCharge->update($validated);

        return redirect()->route('fees.index')->with('success', 'Additional charge updated.');
    }

    public function destroy(AdditionalFeeCharge $additionalFeeCharge): RedirectResponse
    {
        $additionalFeeCharge->delete();

        return redirect()->route('fees.index')->with('success', 'Additional charge deleted.');
    }

    public function receipt(AdditionalFeeCharge $additionalFeeCharge): View
    {
        $additionalFeeCharge->load('student.schoolClass');

        return view('fees.additional-receipt', ['charge' => $additionalFeeCharge]);
    }

    private function generateReceiptNo(): string
    {
        do {
            $receiptNo = 'ADD-'.now()->format('YmdHis').'-'.random_int(100, 999);
        } while (AdditionalFeeCharge::where('receipt_no', $receiptNo)->exists());

        return $receiptNo;
    }
}
