<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Student Profile</h2></x-slot>
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="card space-y-2">
            <h3 class="text-xl font-semibold text-slate-800">{{ $student->name }} ({{ $student->student_id }})</h3>
            <p class="text-sm text-slate-600">Class: {{ $student->schoolClass->class_name }} | Status: {{ $student->status }}</p>
            <p class="text-sm text-slate-600">Mother: {{ $student->mother_name }} | Phone: {{ $student->phone }}</p>
            <p class="text-sm text-slate-600">Age/Gender: {{ $student->age }} / {{ ucfirst($student->gender) }}</p>
            <p class="text-sm text-slate-600">Registration Date: {{ $student->registration_date->format('Y-m-d') }}</p>
            <div class="flex flex-wrap gap-2 pt-2">
                <a href="{{ route('students.edit', $student) }}" class="btn-secondary text-sm">Edit</a>
                <a href="{{ route('exams.index', ['student_id' => $student->id]) }}" class="btn-secondary text-sm">View exams</a>
            </div>
        </div>

        @if($student->additionalFeeCharges->isNotEmpty())
            <div class="card">
                <h4 class="font-semibold text-slate-800">Recent additional charges</h4>
                <ul class="mt-2 divide-y divide-slate-100 text-sm">
                    @foreach($student->additionalFeeCharges as $charge)
                        <li class="flex flex-wrap justify-between gap-2 py-2">
                            <span>{{ $charge->date->format('Y-m-d') }} — {{ \App\Models\AdditionalFeeCharge::CATEGORIES[$charge->category] ?? $charge->category }}</span>
                            <span class="text-slate-600">Total {{ number_format((float) $charge->total_amount, 2) }} | Bal {{ number_format((float) $charge->balance, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-app-layout>
