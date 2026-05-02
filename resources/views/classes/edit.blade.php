<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Edit Class</h2></x-slot>
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('classes.update', $schoolClass) }}" class="card grid gap-4">
            @csrf
            @method('PUT')
            <div>
                <x-input-label for="class_name" value="Class name" />
                <input id="class_name" name="class_name" value="{{ old('class_name', $schoolClass->class_name) }}" class="form-control mt-1" required>
            </div>
            <div>
                <x-input-label for="classroom" value="Classroom" />
                <input id="classroom" name="classroom" value="{{ old('classroom', $schoolClass->classroom) }}" class="form-control mt-1" placeholder="Optional">
            </div>
            <div>
                <x-input-label for="monthly_fee_amount" value="Default monthly fee" />
                <input id="monthly_fee_amount" type="number" step="0.01" min="0" name="monthly_fee_amount" value="{{ old('monthly_fee_amount', $schoolClass->monthly_fee_amount) }}" class="form-control mt-1" required>
            </div>
            <div>
                <x-input-label for="shift" value="Shift" />
                <select id="shift" name="shift" class="form-control mt-1" required>
                    @foreach(['morning' => 'Morning', 'afternoon' => 'Afternoon', 'evening' => 'Evening'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('shift', $schoolClass->shift) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded border-slate-300" @checked(old('is_active', $schoolClass->is_active))>
                <x-input-label for="is_active" value="Class is active (available for new student assignments)" class="!mb-0" />
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Update</button>
                <a href="{{ route('classes.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
