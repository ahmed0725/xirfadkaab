<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Record Monthly Fee Payment</h2></x-slot>
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('fees.store') }}" class="card grid gap-4">
            @csrf
            <select name="student_id" class="rounded-lg border-slate-300 p-2 text-sm" required>
                <option value="">Select student</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}" data-fee="{{ $student->schoolClass?->monthly_fee_amount ?? 0 }}">
                        {{ $student->name }} ({{ $student->student_id }}) - {{ $student->schoolClass?->class_name }}
                    </option>
                @endforeach
            </select>
            <div class="grid gap-3 md:grid-cols-2">
                <select name="fee_month" class="rounded-lg border-slate-300 p-2 text-sm" required>
                    @foreach($months as $monthValue => $monthName)
                        <option value="{{ $monthValue }}" @selected((int)old('fee_month', now()->month) === $monthValue)>{{ $monthName }}</option>
                    @endforeach
                </select>
                <input type="number" name="fee_year" value="{{ old('fee_year', now()->year) }}" class="rounded-lg border-slate-300 p-2 text-sm" min="2000" max="2100" required>
            </div>
            <input id="expected_amount" type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="rounded-lg border-slate-300 p-2 text-sm" placeholder="Expected monthly amount" required>
            <input type="number" step="0.01" name="paid" value="{{ old('paid') }}" class="rounded-lg border-slate-300 p-2 text-sm" placeholder="Paid amount" required>
            <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" class="rounded-lg border-slate-300 p-2 text-sm" required>
            <button class="btn-primary w-fit">Save Payment</button>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const studentSelect = document.querySelector('select[name="student_id"]');
            const amountInput = document.getElementById('expected_amount');
            studentSelect?.addEventListener('change', () => {
                const selected = studentSelect.options[studentSelect.selectedIndex];
                if (selected?.dataset?.fee) amountInput.value = selected.dataset.fee;
            });
        });
    </script>
</x-app-layout>
