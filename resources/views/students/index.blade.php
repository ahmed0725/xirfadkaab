<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Students</h2></x-slot>
    <div class="space-y-4">
        <form method="GET" class="card grid gap-3 md:grid-cols-4">
            <input name="search" value="{{ request('search') }}" class="rounded-lg border-slate-300 p-2 text-sm" placeholder="Search name or ID">
            <select name="school_class_id" class="rounded-lg border-slate-300 p-2 text-sm">
                <option value="">All classes</option>
                @foreach($classes as $class)<option value="{{ $class->id }}" @selected(request('school_class_id')==$class->id)>{{ $class->display_name }}</option>@endforeach
            </select>
            <select name="status" class="rounded-lg border-slate-300 p-2 text-sm">
                <option value="">All status</option><option value="active" @selected(request('status')==='active')>Active</option><option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
            </select>
            <button class="btn-primary">Filter</button>
        </form>
        <div><a href="{{ route('students.create') }}" class="btn-primary">Add Student</a></div>
        <div class="table-shell overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50 text-left text-slate-600"><th class="p-3">ID</th><th class="p-3">Name</th><th class="p-3">Class</th><th class="p-3">Status</th><th class="p-3">Fee Status</th><th class="p-3">Actions</th></tr></thead>
                <tbody>
                @foreach($students as $student)
                    <tr class="border-t border-slate-100 hover:bg-slate-50"><td class="p-3">{{ $student->student_id }}</td><td class="p-3">{{ $student->name }}</td><td class="p-3">{{ $student->schoolClass->display_name }}</td><td class="p-3"><span class="rounded-full px-2 py-1 text-xs {{ $student->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $student->status }}</span></td>
                        <td class="p-3"><span class="rounded-full px-2 py-1 text-xs {{ $student->isFree() ? 'bg-sky-100 text-sky-700' : 'bg-indigo-100 text-indigo-700' }}">{{ $student->isFree() ? 'Free' : 'Regular' }}</span></td>
                        <td class="p-3 space-x-2"><a class="text-blue-600" href="{{ route('students.show',$student) }}">View</a><a class="text-indigo-600" href="{{ route('students.edit',$student) }}">Edit</a>
                            @if(auth()->user()->role==='admin')<form method="POST" action="{{ route('students.destroy',$student) }}" class="inline">@csrf @method('DELETE') <button class="text-red-600">Delete</button></form>@endif
                        </td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $students->links() }}</div>
    </div>
</x-app-layout>
