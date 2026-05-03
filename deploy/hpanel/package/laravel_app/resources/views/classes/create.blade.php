<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Create Class</h2></x-slot>
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('classes.store') }}" class="card grid gap-4" id="class-form">
            @csrf
            <div>
                <x-input-label for="class_name" value="Class name" />
                <input id="class_name" name="class_name" value="{{ old('class_name') }}" class="form-control mt-1" required placeholder="e.g. Electrical">
            </div>
            <div>
                <x-input-label for="course_type_id" value="Course type" />
                <select id="course_type_id" name="course_type_id" class="form-control mt-1" required>
                    <option value="">Select course type</option>
                    @foreach($courseTypes as $ct)
                        <option value="{{ $ct->id }}" @selected(old('course_type_id') == $ct->id)>{{ $ct->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <x-input-label for="start_date" value="Start date" />
                    <input id="start_date" type="date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="form-control mt-1" required>
                </div>
                <div>
                    <x-input-label for="duration_months" value="Duration (months)" />
                    <input id="duration_months" type="number" name="duration_months" min="1" max="120" value="{{ old('duration_months', 6) }}" class="form-control mt-1" required>
                </div>
                <div>
                    <x-input-label value="End date (calculated)" />
                    <p id="end_date_preview" class="mt-2 text-sm text-slate-700 font-medium">—</p>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-input-label for="class_time" value="Class time" />
                    <input id="class_time" type="time" name="class_time" value="{{ old('class_time', '16:00') }}" class="form-control mt-1" required>
                </div>
                <div>
                    <x-input-label for="shift" value="Shift" />
                    <select id="shift" name="shift" class="form-control mt-1" required>
                        @foreach(['morning' => 'Morning', 'afternoon' => 'Afternoon', 'evening' => 'Evening'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('shift', 'afternoon') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <x-input-label for="classroom" value="Classroom" />
                <input id="classroom" name="classroom" value="{{ old('classroom') }}" class="form-control mt-1" placeholder="Optional">
            </div>
            <div>
                <x-input-label for="monthly_fee_amount" value="Default monthly fee" />
                <input id="monthly_fee_amount" type="number" step="0.01" min="0" name="monthly_fee_amount" value="{{ old('monthly_fee_amount', '0') }}" class="form-control mt-1" required>
            </div>
            <div>
                <x-input-label for="teacher_ids" value="Teachers (optional)" />
                <select id="teacher_ids" name="teacher_ids[]" multiple class="form-control mt-1 min-h-[8rem]" size="6">
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(collect(old('teacher_ids'))->contains($teacher->id))>{{ $teacher->name }} ({{ $teacher->email }})</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Hold Ctrl/Cmd to select multiple.</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded border-slate-300" @checked(old('is_active', true))>
                <x-input-label for="is_active" value="Class is active (available for new student assignments)" class="!mb-0" />
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Save</button>
                <a href="{{ route('classes.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <script>
        (function () {
            const start = document.getElementById('start_date');
            const dur = document.getElementById('duration_months');
            const out = document.getElementById('end_date_preview');
            function pad(n) { return n < 10 ? '0' + n : '' + n; }
            function update() {
                if (!start.value || !dur.value) { out.textContent = '—'; return; }
                const d = new Date(start.value + 'T12:00:00');
                if (isNaN(d.getTime())) { out.textContent = '—'; return; }
                const months = parseInt(dur.value, 10);
                d.setMonth(d.getMonth() + months);
                out.textContent = d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            }
            start.addEventListener('change', update);
            dur.addEventListener('input', update);
            update();
        })();
    </script>
</x-app-layout>
