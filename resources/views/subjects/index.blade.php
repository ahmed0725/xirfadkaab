<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Subjects</h2></x-slot>
    <div class="space-y-4">
        <div><a href="{{ route('subjects.create') }}" class="btn-primary">Add Subject</a></div>
        <div class="table-shell overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50 text-left text-slate-600"><th class="p-3">Subject</th><th class="p-3">Class</th><th class="p-3">Actions</th></tr></thead>
                <tbody>
                    @foreach($subjects as $subject)
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="p-3">{{ $subject->subject_name }}</td>
                            <td class="p-3">{{ $subject->schoolClass->class_name }}</td>
                            <td class="p-3">
                                <a class="text-blue-600" href="{{ route('subjects.show',$subject) }}">View</a>
                                <a class="ml-2 text-indigo-600" href="{{ route('subjects.edit',$subject) }}">Edit</a>
                                @if(auth()->user()->role==='admin')
                                    <form class="inline" method="POST" action="{{ route('subjects.destroy',$subject) }}">@csrf @method('DELETE')<button class="ml-2 text-red-600">Delete</button></form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $subjects->links() }}</div>
    </div>
</x-app-layout>
