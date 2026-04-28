<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Edit Fee Payment</h2></x-slot>
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('fees.update', $fee) }}" class="card grid gap-4">
            @csrf
            @method('PATCH')
            <select name="student_id" class="rounded-lg border-slate-300 p-2 text-sm" required>
                @foreach($students as $student)
                    <option value="{{ $student->id }}" @selected(old('student_id', $fee->student_id) == $student->id)>{{ $student->name }} ({{ $student->student_id }})</option>
                @endforeach
            </select>
            <div class="grid gap-3 md:grid-cols-2">
                <select name="fee_month" class="rounded-lg border-slate-300 p-2 text-sm" required>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected((int)old('fee_month', $fee->fee_month) === $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endfor
                </select>
                <input type="number" name="fee_year" value="{{ old('fee_year', $fee->fee_year) }}" class="rounded-lg border-slate-300 p-2 text-sm" min="2000" max="2100" required>
            </div>
            <input type="number" step="0.01" name="amount" value="{{ old('amount', $fee->amount) }}" class="rounded-lg border-slate-300 p-2 text-sm" required>
            <input type="number" step="0.01" name="paid" value="{{ old('paid', $fee->paid) }}" class="rounded-lg border-slate-300 p-2 text-sm" required>
            <input type="date" name="date" value="{{ old('date', $fee->date->format('Y-m-d')) }}" class="rounded-lg border-slate-300 p-2 text-sm" required>
            <div class="flex gap-2">
                <button class="btn-primary">Update Payment</button>
                <a href="{{ route('fees.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
