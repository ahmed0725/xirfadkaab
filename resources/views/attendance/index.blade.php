<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Attendance History</h2></x-slot>
    <div class="space-y-4">
        <div><a href="{{ route('attendance.create') }}" class="btn-primary">Mark Attendance</a></div>
        <form method="GET" class="card grid gap-3 md:grid-cols-4">
            <input type="date" name="date" value="{{ request('date') }}" class="rounded-lg border-slate-300 p-2 text-sm">
            <select name="school_class_id" class="rounded-lg border-slate-300 p-2 text-sm">
                <option value="">All classes</option>
                @foreach($classes as $class)<option value="{{ $class->id }}" @selected(request('school_class_id')==$class->id)>{{ $class->display_name }}</option>@endforeach
            </select>
            <button class="btn-primary">Filter</button>
        </form>
        <div class="table-shell overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50 text-left text-slate-600"><th class="p-3">Date</th><th class="p-3">Student</th><th class="p-3">Class</th><th class="p-3">Subject</th><th class="p-3">Status</th><th class="p-3">Note</th><th class="p-3">Actions</th></tr></thead>
                <tbody>
                    @foreach($attendances as $attendance)
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="p-3">{{ $attendance->date->format('Y-m-d') }}</td>
                            <td class="p-3">{{ $attendance->student->name }}</td>
                            <td class="p-3">{{ $attendance->schoolClass->display_name }}</td>
                            <td class="p-3">{{ $attendance->subject?->subject_name ?? '-' }}</td>
                            <td class="p-3">{{ ucfirst($attendance->status) }}</td>
                            <td class="p-3">{{ $attendance->note }}</td>
                            <td class="p-3">
                                <a href="{{ route('attendance.edit', $attendance) }}" class="text-indigo-600">Edit</a>
                                @if(auth()->user()->role === 'admin')
                                    <form action="{{ route('attendance.destroy', $attendance) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ml-2 text-rose-600" onclick="return confirm('Delete this attendance record?')">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $attendances->links() }}</div>
    </div>
</x-app-layout>
