<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Fees</h2></x-slot>
    <div class="space-y-8">
        <div class="space-y-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Monthly tuition</h3>
            @if(auth()->user()->role === 'admin')
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('fees.create') }}" class="btn-primary">Record tuition payment</a>
                    <a href="{{ route('additional-fees.create') }}" class="btn-secondary">Record additional charge</a>
                </div>
            @endif
            <form method="GET" class="card grid gap-3 md:grid-cols-6">
                <select name="month" class="rounded-lg border-slate-300 p-2 text-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected((int)$filters['month'] === $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endfor
                </select>
                <input type="number" name="year" value="{{ $filters['year'] }}" class="rounded-lg border-slate-300 p-2 text-sm" min="2000" max="2100">
                <select name="school_class_id" class="rounded-lg border-slate-300 p-2 text-sm">
                    <option value="">All classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected((string)$filters['classId'] === (string)$class->id)>{{ $class->display_name }}</option>
                    @endforeach
                </select>
                <select name="student_id" class="rounded-lg border-slate-300 p-2 text-sm">
                    <option value="">All students</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" @selected((string)$filters['studentId'] === (string)$student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>
                <select name="payment_status" class="rounded-lg border-slate-300 p-2 text-sm">
                    <option value="">Paid + Pending</option>
                    <option value="paid" @selected($filters['paymentStatus'] === 'paid')>Paid</option>
                    <option value="pending" @selected($filters['paymentStatus'] === 'pending')>Pending</option>
                </select>
                <input type="hidden" name="additional_category" value="{{ $filters['additionalCategory'] ?? '' }}">
                <button class="btn-primary">Apply Filters</button>
            </form>
            <div class="table-shell overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-slate-600">
                            <th class="p-3">Month</th>
                            <th class="p-3">Student</th>
                            <th class="p-3">Class</th>
                            <th class="p-3">Amount</th>
                            <th class="p-3">Paid</th>
                            <th class="p-3">Balance</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fees as $fee)
                            <tr class="border-t border-slate-100 hover:bg-slate-50">
                                <td class="p-3">{{ \Carbon\Carbon::create()->month($fee->fee_month)->format('F') }} {{ $fee->fee_year }}</td>
                                <td class="p-3">{{ $fee->student->name }}</td>
                                <td class="p-3">{{ $fee->student->schoolClass?->display_name }}</td>
                                <td class="p-3">{{ number_format($fee->amount, 2) }}</td>
                                <td class="p-3">{{ number_format($fee->paid, 2) }}</td>
                                <td class="p-3">{{ number_format($fee->balance, 2) }}</td>
                                <td class="p-3">
                                    <a class="text-blue-600" href="{{ route('fees.receipt', $fee) }}">Receipt</a>
                                    <a class="ml-2 text-slate-700" href="{{ route('fees.receipt', $fee) }}" target="_blank">Print</a>
                                    @if(auth()->user()->role === 'admin')
                                        <a class="ml-2 text-indigo-600" href="{{ route('fees.edit', $fee) }}">Edit</a>
                                        <form method="POST" action="{{ route('fees.destroy', $fee) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-2 text-rose-600" onclick="return confirm('Delete this payment record?')">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $fees->links() }}</div>

            <div class="grid gap-4 md:grid-cols-2">
                @foreach($classSummaries as $summary)
                    <div class="card">
                        <h3 class="font-semibold">{{ $summary['class']->display_name }}</h3>
                        <p class="text-sm text-slate-600">Paid students: {{ $summary['paid_students']->count() }} | Pending students: {{ $summary['pending_students']->count() }}</p>
                        <p class="text-sm text-slate-600">Total paid: {{ number_format($summary['total_paid'], 2) }} | Total pending: {{ number_format($summary['total_pending'], 2) }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-4 border-t border-slate-200 pt-8">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Additional charges (books, certificates, other)</h3>
            <form method="GET" class="card grid gap-3 md:grid-cols-5">
                <input type="hidden" name="month" value="{{ $filters['month'] }}">
                <input type="hidden" name="year" value="{{ $filters['year'] }}">
                <input type="hidden" name="payment_status" value="{{ $filters['paymentStatus'] ?? '' }}">
                <select name="school_class_id" class="rounded-lg border-slate-300 p-2 text-sm">
                    <option value="">All classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected((string)$filters['classId'] === (string)$class->id)>{{ $class->display_name }}</option>
                    @endforeach
                </select>
                <select name="student_id" class="rounded-lg border-slate-300 p-2 text-sm">
                    <option value="">All students</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" @selected((string)$filters['studentId'] === (string)$student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>
                <select name="additional_category" class="rounded-lg border-slate-300 p-2 text-sm">
                    <option value="">All categories</option>
                    @foreach(\App\Models\AdditionalFeeCharge::CATEGORIES as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['additionalCategory'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn-primary md:col-span-2">Apply to additional list</button>
            </form>
            <div class="table-shell overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-slate-600">
                            <th class="p-3">Date</th>
                            <th class="p-3">Student</th>
                            <th class="p-3">Class</th>
                            <th class="p-3">Category</th>
                            <th class="p-3">Total</th>
                            <th class="p-3">Paid</th>
                            <th class="p-3">Balance</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($additionalCharges as $charge)
                            <tr class="border-t border-slate-100 hover:bg-slate-50">
                                <td class="p-3">{{ $charge->date->format('Y-m-d') }}</td>
                                <td class="p-3">{{ $charge->student->name }}</td>
                                <td class="p-3">{{ $charge->student->schoolClass?->display_name }}</td>
                                <td class="p-3">{{ \App\Models\AdditionalFeeCharge::CATEGORIES[$charge->category] ?? $charge->category }}</td>
                                <td class="p-3">{{ number_format((float) $charge->total_amount, 2) }}</td>
                                <td class="p-3">{{ number_format((float) $charge->paid, 2) }}</td>
                                <td class="p-3">{{ number_format((float) $charge->balance, 2) }}</td>
                                <td class="p-3 whitespace-nowrap">
                                    <a class="text-blue-600" href="{{ route('additional-fees.receipt', $charge) }}">Receipt</a>
                                    @if(auth()->user()->role === 'admin')
                                        <a class="ml-2 text-indigo-600" href="{{ route('additional-fees.edit', $charge) }}">Edit</a>
                                        <form method="POST" action="{{ route('additional-fees.destroy', $charge) }}" class="inline" onsubmit="return confirm('Delete this charge?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-2 text-rose-600">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="p-4 text-center text-slate-500">No additional charges for this filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $additionalCharges->links() }}</div>
        </div>
    </div>
</x-app-layout>
