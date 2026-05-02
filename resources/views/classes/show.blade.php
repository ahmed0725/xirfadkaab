<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Class Details</h2></x-slot>
    <div class="mx-auto grid max-w-6xl gap-4 md:grid-cols-2">
        <div class="card space-y-2">
            <h3 class="text-lg font-semibold text-slate-800">{{ $schoolClass->class_name }}</h3>
            <p class="text-sm text-slate-600">Classroom: {{ $schoolClass->classroom ?: '—' }}</p>
            <p class="text-sm text-slate-600">Shift: <span class="capitalize">{{ $schoolClass->shift }}</span></p>
            <p class="text-sm text-slate-600">Default monthly fee: {{ number_format((float) $schoolClass->monthly_fee_amount, 2) }}</p>
            <p class="text-sm text-slate-600">
                Status:
                @if($schoolClass->is_active)
                    <span class="font-medium text-emerald-700">Active</span>
                @else
                    <span class="font-medium text-slate-600">Inactive</span>
                @endif
            </p>
            <div class="pt-2">
                <a href="{{ route('classes.edit', $schoolClass) }}" class="btn-primary text-sm">Edit class</a>
            </div>
        </div>
        <div class="card">
            <h4 class="font-semibold text-slate-800">Students</h4>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-700">
                @forelse($schoolClass->students as $student)
                    <li><a class="text-blue-600 hover:underline" href="{{ route('students.show', $student) }}">{{ $student->name }}</a> ({{ $student->student_id }})</li>
                @empty
                    <li class="list-none pl-0 text-slate-500">No students in this class.</li>
                @endforelse
            </ul>
        </div>
        <div class="card md:col-span-2">
            <h4 class="font-semibold text-slate-800">Subjects</h4>
            <ul class="mt-2 flex flex-wrap gap-2">
                @foreach($schoolClass->subjects as $subject)
                    <li class="rounded-lg bg-slate-100 px-3 py-1 text-sm text-slate-700">{{ $subject->subject_name }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</x-app-layout>
