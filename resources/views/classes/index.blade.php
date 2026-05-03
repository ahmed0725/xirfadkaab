<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Classes</h2></x-slot>
    <div class="space-y-4">
        @can('create', App\Models\SchoolClass::class)
            <div><a href="{{ route('classes.create') }}" class="btn-primary">Add Class</a></div>
        @endcan

        <form method="GET" class="card grid gap-3 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search class name" class="rounded-lg border-slate-300 p-2 text-sm">
            <select name="course_type_id" class="rounded-lg border-slate-300 p-2 text-sm">
                <option value="">All course types</option>
                @foreach($courseTypes as $ct)
                    <option value="{{ $ct->id }}" @selected((string) request('course_type_id') === (string) $ct->id)>{{ $ct->name }}</option>
                @endforeach
            </select>
            <select name="shift" class="rounded-lg border-slate-300 p-2 text-sm">
                <option value="">All shifts</option>
                @foreach(['morning' => 'Morning', 'afternoon' => 'Afternoon', 'evening' => 'Evening'] as $v => $label)
                    <option value="{{ $v }}" @selected(request('shift') === $v)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="class_time" class="rounded-lg border-slate-300 p-2 text-sm">
                <option value="">All times</option>
                @foreach($timeOptions as $t)
                    <option value="{{ $t }}" @selected(request('class_time') === $t)>{{ \Carbon\Carbon::parse($t)->format('g:i A') }}</option>
                @endforeach
            </select>
            <select name="is_active" class="rounded-lg border-slate-300 p-2 text-sm">
                <option value="">Active + inactive</option>
                <option value="1" @selected(request('is_active') === '1')>Active only</option>
                <option value="0" @selected(request('is_active') === '0')>Inactive only</option>
            </select>
            <div class="flex gap-2 md:col-span-2 lg:col-span-4 xl:col-span-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('classes.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-shell overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-slate-600">
                        <th class="p-3">Class</th>
                        <th class="p-3">Course type</th>
                        <th class="p-3">Time</th>
                        <th class="p-3">Timeline</th>
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
                            <td class="p-3">{{ $class->courseType?->name ?? '—' }}</td>
                            <td class="p-3">{{ $class->formattedClassTime() }}</td>
                            <td class="p-3 whitespace-nowrap text-xs">
                                {{ $class->start_date?->format('M j, Y') }} → {{ $class->end_date?->format('M j, Y') }}
                                <span class="block text-slate-500">{{ $class->duration_months }} mo.</span>
                            </td>
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
                                @can('update', $class)
                                    <a class="ml-2 text-indigo-600" href="{{ route('classes.edit', $class) }}">Edit</a>
                                @endcan
                                @can('delete', $class)
                                    <form method="POST" class="inline" action="{{ route('classes.destroy', $class) }}" onsubmit="return confirm('Delete this class?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-2 text-red-600">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $classes->links() }}</div>
    </div>
</x-app-layout>
