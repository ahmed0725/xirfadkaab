<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Record Monthly Fee Payment</h2></x-slot>
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('fees.store') }}" class="card grid gap-4" x-data="{}" @student-selected.window="document.getElementById('expected_amount').value = $event.detail.monthly_fee">
            @csrf
            <x-student-search-select
                name="student_id"
                label="Student"
                :selected-id="old('student_id')"
                tuition-only
                input-class="rounded-lg border-slate-300 p-2 text-sm w-full"
            />
            <p class="text-xs text-slate-500 -mt-2">Only regular (tuition-paying) students appear in this search.</p>
            <div class="grid gap-3 md:grid-cols-2">
                <select name="fee_month" class="rounded-lg border-slate-300 p-2 text-sm" required>
                    @foreach($months as $monthValue => $monthName)
                        <option value="{{ $monthValue }}" @selected((int)old('fee_month', now()->month) === $monthValue)>{{ $monthName }}</option>
                    @endforeach
                </select>
                <input type="number" name="fee_year" value="{{ old('fee_year', now()->year) }}" class="rounded-lg border-slate-300 p-2 text-sm" min="2000" max="2100" required>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="custom_amount" value="1" class="rounded border-slate-300" @checked(old('custom_amount'))>
                    <span>Custom tuition amount (override class default)</span>
                </label>
            </div>
            <input id="expected_amount" type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="rounded-lg border-slate-300 p-2 text-sm" placeholder="Fills from class when student is selected" required>
            <input type="number" step="0.01" name="paid" value="{{ old('paid') }}" class="rounded-lg border-slate-300 p-2 text-sm" placeholder="Paid amount" required>
            <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" class="rounded-lg border-slate-300 p-2 text-sm" required>
            <button class="btn-primary w-fit">Save Payment</button>
        </form>
    </div>
</x-app-layout>
