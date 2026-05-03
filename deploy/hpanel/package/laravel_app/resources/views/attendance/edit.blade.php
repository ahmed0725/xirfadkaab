<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Edit Attendance</h2></x-slot>
    <div class="mx-auto max-w-4xl">
        <form method="POST" action="{{ route('attendance.update', $attendance) }}" class="card grid gap-4 md:grid-cols-2">
            @csrf
            @method('PATCH')
            <input type="date" name="date" value="{{ old('date', $attendance->date->format('Y-m-d')) }}" class="rounded-lg border-slate-300 p-2 text-sm" required>
            <select name="school_class_id" class="rounded-lg border-slate-300 p-2 text-sm" required>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected(old('school_class_id', $attendance->school_class_id) == $class->id)>{{ $class->display_name }}</option>
                @endforeach
            </select>
            <select name="student_id" class="rounded-lg border-slate-300 p-2 text-sm" required>
                @foreach($classes as $class)
                    @foreach($class->students as $student)
                        <option value="{{ $student->id }}" @selected(old('student_id', $attendance->student_id) == $student->id)>
                            {{ $class->display_name }} — {{ $student->name }}
                        </option>
                    @endforeach
                @endforeach
            </select>
            <select name="subject_id" class="rounded-lg border-slate-300 p-2 text-sm">
                <option value="">No Subject</option>
                @foreach($classes as $class)
                    @foreach($class->subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id', $attendance->subject_id) == $subject->id)>
                            {{ $class->display_name }} — {{ $subject->subject_name }}
                        </option>
                    @endforeach
                @endforeach
            </select>
            <select name="status" class="rounded-lg border-slate-300 p-2 text-sm" required>
                <option value="present" @selected(old('status', $attendance->status) === 'present')>Present</option>
                <option value="absent" @selected(old('status', $attendance->status) === 'absent')>Absent</option>
                <option value="late" @selected(old('status', $attendance->status) === 'late')>Late</option>
            </select>
            <input name="note" value="{{ old('note', $attendance->note) }}" class="rounded-lg border-slate-300 p-2 text-sm" placeholder="Note">
            <div class="md:col-span-2 flex gap-2">
                <button class="btn-primary">Update</button>
                <a href="{{ route('attendance.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
