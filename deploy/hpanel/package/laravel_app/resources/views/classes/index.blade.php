<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Classes</h2></x-slot>
    <div class="space-y-4">
        <div><a href="{{ route('classes.create') }}" class="btn-primary">Add Class</a></div>
        <div class="table-shell overflow-x-auto">
            <table class="w-full text-sm"><thead><tr class="bg-slate-50 text-left text-slate-600"><th class="p-3">Class</th><th class="p-3">Classroom</th><th class="p-3">Students</th><th class="p-3">Subjects</th><th class="p-3">Actions</th></tr></thead><tbody>
                @foreach($classes as $class)
                <tr class="border-t border-slate-100 hover:bg-slate-50"><td class="p-3">{{ $class->class_name }}</td><td class="p-3">{{ $class->classroom }}</td><td class="p-3">{{ $class->students_count }}</td><td class="p-3">{{ $class->subjects_count }}</td>
                    <td class="p-3"><a class="text-blue-600" href="{{ route('classes.show',$class) }}">View</a> <a class="text-indigo-600" href="{{ route('classes.edit',$class) }}">Edit</a>
                        @if(auth()->user()->role==='admin')<form method="POST" class="inline" action="{{ route('classes.destroy',$class) }}">@csrf @method('DELETE') <button class="text-red-600">Delete</button></form>@endif
                    </td></tr>
                @endforeach
            </tbody></table>
        </div>
        <div class="mt-4">{{ $classes->links() }}</div>
    </div>
</x-app-layout>
