<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Additional Charge Receipt</h2></x-slot>
    <div class="mx-auto max-w-3xl">
        <div class="card receipt-print">
            <div class="mb-4 flex items-start justify-between border-b border-slate-200 pb-4">
                <div class="flex items-start gap-3">
                    <x-application-logo class="h-12 w-auto shrink-0 text-blue-700" />
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">{{ $systemSettings->school_name ?? 'Xirfad Kaab' }}</h3>
                        @if(! empty($systemSettings->address))
                            <p class="text-xs text-slate-500">{{ $systemSettings->address }}</p>
                        @endif
                        @if(! empty($systemSettings->contact_info))
                            <p class="text-xs text-slate-500">{{ $systemSettings->contact_info }}</p>
                        @endif
                    </div>
                </div>
                <div class="text-right text-xs text-slate-500">
                    <p>Receipt No: {{ $charge->receipt_no }}</p>
                    <p>Date: {{ $charge->date->format('Y-m-d') }}</p>
                </div>
            </div>

            <div class="space-y-2 text-sm">
                <p><span class="font-semibold">Student:</span> {{ $charge->student->name }} ({{ $charge->student->student_id }})</p>
                <p><span class="font-semibold">Class:</span> {{ $charge->student->schoolClass?->display_name ?? '—' }}</p>
                <p><span class="font-semibold">Category:</span> {{ \App\Models\AdditionalFeeCharge::CATEGORIES[$charge->category] ?? ucfirst($charge->category) }}</p>
                @if($charge->title)
                    <p><span class="font-semibold">Description:</span> {{ $charge->title }}</p>
                @endif
                <p><span class="font-semibold">Total:</span> {{ number_format((float) $charge->total_amount, 2) }}</p>
                <p><span class="font-semibold">Paid:</span> {{ number_format((float) $charge->paid, 2) }}</p>
                <p><span class="font-semibold">Balance:</span> {{ number_format((float) $charge->balance, 2) }}</p>
            </div>

            <div class="mt-6 flex justify-end gap-2 print:hidden">
                <button type="button" class="btn-primary" onclick="window.print()">Print</button>
                <a href="{{ route('fees.index') }}" class="btn-secondary">Back</a>
            </div>
        </div>
    </div>
</x-app-layout>
