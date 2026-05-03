<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Course types</h2></x-slot>
    <div class="space-y-4">
        <div><a href="{{ route('course-types.create') }}" class="btn-primary">Add course type</a></div>
        @if ($errors->has('course_type'))
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm text-rose-800">{{ $errors->first('course_type') }}</div>
        @endif
        <div class="table-shell overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-slate-600">
                        <th class="p-3">Name</th>
                        <th class="p-3">Classes</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courseTypes as $courseType)
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="p-3 font-medium text-slate-800">{{ $courseType->name }}</td>
                            <td class="p-3">{{ $courseType->school_classes_count }}</td>
                            <td class="p-3 whitespace-nowrap">
                                <a class="text-indigo-600" href="{{ route('course-types.edit', $courseType) }}">Edit</a>
                                <form method="POST" class="inline" action="{{ route('course-types.destroy', $courseType) }}" onsubmit="return confirm('Delete this course type?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-2 text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $courseTypes->links() }}</div>
    </div>
</x-app-layout>
