<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Fees</h2></x-slot>
    <div class="space-y-4">
        @if(auth()->user()->role==='admin')
            <div><a href="{{ route('fees.create') }}" class="btn-primary">Record Payment</a></div>
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
                    <option value="{{ $class->id }}" @selected((string)$filters['classId'] === (string)$class->id)>{{ $class->class_name }}</option>
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
            <button class="btn-primary">Apply Filters</button>
        </form>
        <div class="table-shell overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50 text-left text-slate-600"><th class="p-3">Month</th><th class="p-3">Student</th><th class="p-3">Class</th><th class="p-3">Amount</th><th class="p-3">Paid</th><th class="p-3">Balance</th><th class="p-3">Actions</th></tr></thead>
                <tbody>
                    @foreach($fees as $fee)
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="p-3">{{ \Carbon\Carbon::create()->month($fee->fee_month)->format('F') }} {{ $fee->fee_year }}</td>
                            <td class="p-3">{{ $fee->student->name }}</td>
                            <td class="p-3">{{ $fee->student->schoolClass?->class_name }}</td>
                            <td class="p-3">{{ number_format($fee->amount,2) }}</td>
                            <td class="p-3">{{ number_format($fee->paid,2) }}</td>
                            <td class="p-3">{{ number_format($fee->balance,2) }}</td>
                            <td class="p-3">
                                <a class="text-blue-600" href="{{ route('fees.receipt',$fee) }}">Receipt</a>
                                <a class="ml-2 text-slate-700" href="{{ route('fees.receipt',$fee) }}" target="_blank">Print</a>
                                @if(auth()->user()->role==='admin')
                                    <a class="ml-2 text-indigo-600" href="{{ route('fees.edit',$fee) }}">Edit</a>
                                    <form method="POST" action="{{ route('fees.destroy', $fee) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ml-2 text-rose-600" onclick="return confirm('Delete this payment record?')">Delete</button>
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
                    <h3 class="font-semibold">{{ $summary['class']->class_name }}</h3>
                    <p class="text-sm text-slate-600">Paid students: {{ $summary['paid_students']->count() }} | Pending students: {{ $summary['pending_students']->count() }}</p>
                    <p class="text-sm text-slate-600">Total paid: {{ number_format($summary['total_paid'], 2) }} | Total pending: {{ number_format($summary['total_pending'], 2) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
