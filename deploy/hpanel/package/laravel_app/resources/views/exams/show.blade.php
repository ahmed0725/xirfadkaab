<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">{{ $exam->title }}</h2></x-slot>
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="card flex flex-wrap items-start justify-between gap-4">
            <div class="text-sm text-slate-600">
                <p><span class="font-semibold text-slate-800">Class:</span> {{ $exam->schoolClass?->display_name }}</p>
                <p><span class="font-semibold text-slate-800">Subject:</span> {{ $exam->subject?->subject_name ?? '—' }}</p>
                <p><span class="font-semibold text-slate-800">Date:</span> {{ $exam->exam_date->format('Y-m-d') }}</p>
                <p><span class="font-semibold text-slate-800">Max marks:</span> {{ number_format((float) $exam->max_marks, 2) }}</p>
                @if($exam->notes)
                    <p class="mt-2 text-slate-700">{{ $exam->notes }}</p>
                @endif
            </div>
            <div class="text-right text-sm">
                @if($stats['count'] > 0)
                    <p class="font-semibold text-slate-800">Class performance (recorded)</p>
                    <p>Average: {{ $stats['average'] }}</p>
                    <p>Min: {{ $stats['min'] }} | Max: {{ $stats['max'] }}</p>
                    <p class="text-slate-500">Based on {{ $stats['count'] }} result(s)</p>
                @else
                    <p class="text-slate-500">No marks recorded yet.</p>
                @endif
            </div>
            @if(in_array(auth()->user()->role, ['admin', 'user'], true))
                <div class="flex w-full flex-wrap gap-2 border-t border-slate-100 pt-4 sm:w-auto sm:border-0 sm:pt-0">
                    <a href="{{ route('exams.edit', $exam) }}" class="btn-secondary text-sm">Edit exam</a>
                    <a href="{{ route('exams.index') }}" class="btn-secondary text-sm">All exams</a>
                </div>
            @endif
        </div>

        <div class="card">
            <h3 class="mb-3 font-semibold text-slate-800">Student results</h3>
            <form method="POST" action="{{ route('exams.results.store', $exam) }}" class="space-y-4">
                @csrf
                <div class="table-shell overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-slate-600">
                                <th class="p-3">Student</th>
                                <th class="p-3">Marks (max {{ number_format((float) $exam->max_marks, 2) }})</th>
                                <th class="p-3">Grade</th>
                                <th class="p-3">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exam->schoolClass->students as $student)
                                @php
                                    $result = $resultsByStudent->get($student->id);
                                    $oldMarks = old('marks.'.$student->id, $result?->marks_obtained);
                                @endphp
                                <tr class="border-t border-slate-100">
                                    <td class="p-3">{{ $student->name }} <span class="text-slate-400">({{ $student->student_id }})</span></td>
                                    <td class="p-3">
                                        <input type="number" step="0.01" min="0" name="marks[{{ $student->id }}]" value="{{ $oldMarks !== null && $oldMarks !== '' ? $oldMarks : '' }}" class="form-control w-28">
                                    </td>
                                    <td class="p-3 text-slate-600">{{ $result?->grade ?? '—' }}</td>
                                    <td class="p-3">
                                        <input type="text" name="remarks[{{ $student->id }}]" value="{{ old('remarks.'.$student->id, $result?->remarks) }}" class="form-control max-w-xs" placeholder="Optional">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn-primary">Save results</button>
            </form>
        </div>
    </div>
</x-app-layout>
