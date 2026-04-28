<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Fee Receipt</h2></x-slot>
    <div class="mx-auto max-w-3xl">
        <div class="card receipt-print">
            <div class="mb-4 flex items-start justify-between border-b border-slate-200 pb-4">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-full bg-blue-100 text-center leading-[3rem] font-bold text-blue-700">XK</div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Xirfad Kaab Schools</h3>
                        <p class="text-xs text-slate-500">Address: Hargeisa, Somaliland</p>
                        <p class="text-xs text-slate-500">Contact: +252 63 0000000 | info@xirfadkaab.test</p>
                    </div>
                </div>
                <div class="text-right text-xs text-slate-500">
                    <p>Receipt No: {{ $fee->receipt_no }}</p>
                    <p>Date: {{ $fee->date->format('Y-m-d') }}</p>
                </div>
            </div>

            <div class="space-y-2 text-sm">
                <p><span class="font-semibold">Student Name:</span> {{ $fee->student->name }}</p>
                <p><span class="font-semibold">Student ID:</span> {{ $fee->student->student_id }}</p>
                <p><span class="font-semibold">Payment Amount:</span> {{ number_format($fee->amount, 2) }}</p>
                <p><span class="font-semibold">Amount Paid:</span> {{ number_format($fee->paid, 2) }}</p>
                <p><span class="font-semibold">Balance Remaining:</span> {{ number_format($fee->balance, 2) }}</p>
            </div>

            <div class="mt-6 flex justify-end gap-2 print:hidden">
                <button class="btn-primary" onclick="window.print()">Print Receipt</button>
                <a href="{{ route('fees.index') }}" class="btn-secondary">Back</a>
            </div>
        </div>
    </div>
</x-app-layout>
