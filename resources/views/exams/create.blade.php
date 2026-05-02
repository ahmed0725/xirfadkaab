<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Create exam</h2></x-slot>
    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('exams.store') }}" class="card grid gap-4">
            @csrf
            <div>
                <x-input-label for="title" value="Title" />
                <input id="title" name="title" value="{{ old('title') }}" class="form-control mt-1" required>
            </div>
            <div>
                <x-input-label for="exam_date" value="Exam date" />
                <input id="exam_date" type="date" name="exam_date" value="{{ old('exam_date', now()->format('Y-m-d')) }}" class="form-control mt-1" required>
            </div>
            <div>
                <x-input-label for="school_class_id" value="Class" />
                <select id="school_class_id" name="school_class_id" class="form-control mt-1" required>
                    <option value="">Select class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->class_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="subject_id" value="Subject (optional)" />
                <select id="subject_id" name="subject_id" class="form-control mt-1">
                    <option value="">Whole class / not subject-specific</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>
                            {{ $subject->subject_name }} — {{ $subject->schoolClass?->class_name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Choose a subject that belongs to the selected class (validation on save).</p>
            </div>
            <div>
                <x-input-label for="max_marks" value="Maximum marks" />
                <input id="max_marks" type="number" step="0.01" min="1" name="max_marks" value="{{ old('max_marks', '100') }}" class="form-control mt-1" required>
            </div>
            <div>
                <x-input-label for="notes" value="Notes" />
                <textarea id="notes" name="notes" rows="2" class="form-control mt-1">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Save</button>
                <a href="{{ route('exams.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
