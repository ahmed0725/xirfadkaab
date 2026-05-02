<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Exams</h2></x-slot>
    <div class="space-y-4">
        @if(in_array(auth()->user()->role, ['admin', 'user'], true))
            <a href="{{ route('exams.create') }}" class="btn-primary">Create exam</a>
        @endif

        <form method="GET" class="card grid gap-3 md:grid-cols-5">
            <select name="school_class_id" class="form-control">
                <option value="">All classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected(($filters['school_class_id'] ?? '') == $class->id)>{{ $class->class_name }}</option>
                @endforeach
            </select>
            <select name="subject_id" class="form-control">
                <option value="">All subjects</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(($filters['subject_id'] ?? '') == $subject->id)>
                        {{ $subject->subject_name }} ({{ $subject->schoolClass?->class_name }})
                    </option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control" placeholder="From">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control" placeholder="To">
            <input type="hidden" name="student_id" value="{{ $filters['student_id'] ?? '' }}">
            <button type="submit" class="btn-primary md:col-span-5 w-fit">Apply filters</button>
        </form>

        <div class="table-shell overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-slate-600">
                        <th class="p-3">Title</th>
                        <th class="p-3">Class</th>
                        <th class="p-3">Subject</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Max marks</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $exam)
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="p-3 font-medium">{{ $exam->title }}</td>
                            <td class="p-3">{{ $exam->schoolClass?->class_name }}</td>
                            <td class="p-3">{{ $exam->subject?->subject_name ?? '—' }}</td>
                            <td class="p-3">{{ $exam->exam_date->format('Y-m-d') }}</td>
                            <td class="p-3">{{ number_format((float) $exam->max_marks, 2) }}</td>
                            <td class="p-3 whitespace-nowrap">
                                <a href="{{ route('exams.show', $exam) }}" class="text-blue-600">View / marks</a>
                                @if(in_array(auth()->user()->role, ['admin', 'user'], true))
                                    <a href="{{ route('exams.edit', $exam) }}" class="ml-2 text-indigo-600">Edit</a>
                                    <form method="POST" action="{{ route('exams.destroy', $exam) }}" class="inline" onsubmit="return confirm('Delete this exam and all results?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-2 text-rose-600">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-4 text-center text-slate-500">No exams found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $exams->links() }}</div>
    </div>
</x-app-layout>
