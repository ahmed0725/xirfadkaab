<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Record Additional Charge</h2></x-slot>
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('additional-fees.store') }}" class="card grid gap-4">
            @csrf
            <div>
                <x-input-label for="student_id" value="Student" />
                <select id="student_id" name="student_id" class="form-control mt-1" required>
                    <option value="">Select student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->name }} ({{ $student->student_id }}) — {{ $student->schoolClass?->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="category" value="Category" />
                <select id="category" name="category" class="form-control mt-1" required>
                    @foreach(\App\Models\AdditionalFeeCharge::CATEGORIES as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="title" value="Description (optional)" />
                <input id="title" type="text" name="title" value="{{ old('title') }}" class="form-control mt-1" placeholder="e.g. Term 1 workbook set">
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-input-label for="total_amount" value="Total amount" />
                    <input id="total_amount" type="number" step="0.01" min="0" name="total_amount" value="{{ old('total_amount') }}" class="form-control mt-1" required>
                </div>
                <div>
                    <x-input-label for="paid" value="Amount paid" />
                    <input id="paid" type="number" step="0.01" min="0" name="paid" value="{{ old('paid', '0') }}" class="form-control mt-1" required>
                </div>
            </div>
            <div>
                <x-input-label for="date" value="Date" />
                <input id="date" type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" class="form-control mt-1" required>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Save</button>
                <a href="{{ route('fees.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
