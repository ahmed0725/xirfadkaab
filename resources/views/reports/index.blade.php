<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Reports Center</h2></x-slot>
    <div class="space-y-4">
        <form method="GET" class="card grid gap-3 md:grid-cols-6">
            <select name="report_type" class="rounded-lg border-slate-300 p-2 text-sm md:col-span-2">
                @foreach($reportTypes as $type => $label)
                    <option value="{{ $type }}" @selected($selectedReportType === $type)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="month" class="rounded-lg border-slate-300 p-2 text-sm">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected((int)$filters['month'] === $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                @endfor
            </select>
            <input type="number" name="year" value="{{ $filters['year'] }}" class="rounded-lg border-slate-300 p-2 text-sm" min="2000" max="2100">
            <select name="school_class_id" class="rounded-lg border-slate-300 p-2 text-sm">
                <option value="">All classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected((string)$filters['classId'] === (string)$class->id)>{{ $class->class_name }}</option>
                @endforeach
            </select>
            <select name="student_id" class="rounded-lg border-slate-300 p-2 text-sm">
                <option value="">All students</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}" @selected((string)$filters['studentId'] === (string)$student->id)>{{ $student->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="btn-primary">Run</button>
                <a href="{{ route('reports.print', request()->query()) }}" target="_blank" class="btn-secondary">Print</a>
                <a href="{{ route('reports.pdf', request()->query()) }}" class="btn-secondary">PDF</a>
            </div>
        </form>

        <div class="card">
            <h3 class="font-semibold">{{ $selectedReportLabel }}</h3>
            <p class="text-sm text-slate-600">Period: {{ $periodLabel }}</p>
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            <div class="card xl:col-span-2">
                <div class="table-shell overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-slate-600">
                                @foreach($reportTable['columns'] as $column)
                                    <th class="p-3">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportTable['rows'] as $row)
                                <tr class="border-t border-slate-100 hover:bg-slate-50">
                                    @foreach($row as $cell)
                                        <td class="p-3">{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td class="p-3 text-slate-500" colspan="{{ count($reportTable['columns']) }}">No records for current filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card">
                <h3 class="mb-3 font-semibold">Summary</h3>
                <ul class="space-y-2 text-sm text-slate-700">
                    @foreach($reportSummary as $key => $value)
                        <li class="flex justify-between gap-4 border-b border-slate-100 pb-1">
                            <span>{{ $key }}</span>
                            <strong>{{ $value }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
