<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Classes</h2></x-slot>
    <div class="space-y-4">
        <div><a href="{{ route('classes.create') }}" class="btn-primary">Add Class</a></div>
        <div class="table-shell overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-slate-600">
                        <th class="p-3">Class</th>
                        <th class="p-3">Classroom</th>
                        <th class="p-3">Shift</th>
                        <th class="p-3">Monthly fee</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Students</th>
                        <th class="p-3">Subjects</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classes as $class)
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="p-3 font-medium text-slate-800">{{ $class->class_name }}</td>
                            <td class="p-3">{{ $class->classroom ?: '—' }}</td>
                            <td class="p-3 capitalize">{{ $class->shift }}</td>
                            <td class="p-3">{{ number_format((float) $class->monthly_fee_amount, 2) }}</td>
                            <td class="p-3">
                                @if($class->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
                                @endif
                            </td>
                            <td class="p-3">{{ $class->students_count }}</td>
                            <td class="p-3">{{ $class->subjects_count }}</td>
                            <td class="p-3 whitespace-nowrap">
                                <a class="text-blue-600" href="{{ route('classes.show', $class) }}">View</a>
                                <a class="ml-2 text-indigo-600" href="{{ route('classes.edit', $class) }}">Edit</a>
                                @if(auth()->user()->role === 'admin')
                                    <form method="POST" class="inline" action="{{ route('classes.destroy', $class) }}" onsubmit="return confirm('Delete this class?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-2 text-red-600">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $classes->links() }}</div>
    </div>
</x-app-layout>
