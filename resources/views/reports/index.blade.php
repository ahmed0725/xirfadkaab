<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('reports.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">&larr; All reports</a>
            <span class="text-slate-300">/</span>
            <h2 class="text-xl font-semibold text-slate-800">{{ $currentGroup }} Reports</h2>
        </div>
    </x-slot>
    <div class="space-y-4">
        <div class="flex flex-wrap gap-2">
            @foreach($groupReports as $type => $label)
                <a href="{{ route('reports.index', array_merge(request()->except('report_type'), ['report_type' => $type])) }}"
                   class="rounded-full px-3 py-1.5 text-sm font-medium transition {{ $selectedReportType === $type ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form method="GET" class="card space-y-4">
            <input type="hidden" name="report_type" value="{{ $selectedReportType }}">
            @if(count($activeFilters) > 0)
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    @if(in_array('month', $activeFilters))
                        <div>
                            <label for="filter_month" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Month</label>
                            <select id="filter_month" name="month" class="w-full rounded-lg border-slate-300 p-2 text-sm">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected((int)$filters['month'] === $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                    @endif
                    @if(in_array('year', $activeFilters))
                        <div>
                            <label for="filter_year" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Year</label>
                            <input id="filter_year" type="number" name="year" value="{{ $filters['year'] }}" class="w-full rounded-lg border-slate-300 p-2 text-sm" min="2000" max="2100">
                        </div>
                    @endif
                    @if(in_array('class', $activeFilters))
                        <div>
                            <label for="filter_class" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Class</label>
                            <select id="filter_class" name="school_class_id" class="w-full rounded-lg border-slate-300 p-2 text-sm">
                                <option value="">All classes</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" @selected((string)$filters['classId'] === (string)$class->id)>{{ $class->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(in_array('student', $activeFilters))
                        <div>
                            <label for="filter_student" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Student</label>
                            <select id="filter_student" name="student_id" class="w-full rounded-lg border-slate-300 p-2 text-sm">
                                <option value="">All students</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" @selected((string)$filters['studentId'] === (string)$student->id)>{{ $student->name }} ({{ $student->student_id }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(in_array('expense_category', $activeFilters))
                        <div>
                            <label for="filter_expense_category" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Expense type</label>
                            <select id="filter_expense_category" name="expense_category" class="w-full rounded-lg border-slate-300 p-2 text-sm">
                                <option value="">All expense types</option>
                                @foreach(\App\Models\Expense::CATEGORIES as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['expenseCategory'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            @else
                <p class="text-sm text-slate-500">This report has no filters — it always covers all records.</p>
            @endif
            <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
                @if(count($activeFilters) > 0)
                    <button class="btn-primary">Run Report</button>
                @endif
                <a href="{{ route('reports.print', request()->query()) }}" target="_blank" class="btn-secondary">Print</a>
                <a href="{{ route('reports.pdf', request()->query()) }}" class="btn-secondary">PDF</a>
            </div>
        </form>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            @foreach($overviewStats as $label => $value)
                <div class="metric-card">
                    <p class="text-sm text-slate-500">{{ $label }}</p>
                    <p class="mt-1 text-2xl font-bold text-slate-800">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="card">
            <div class="flex items-center gap-3">
                <x-application-logo class="h-10 w-auto" />
                <div>
                    <h3 class="font-semibold">{{ $selectedReportLabel }}</h3>
                    <p class="text-sm text-slate-600">Period: {{ $periodLabel }}</p>
                </div>
            </div>
            @if(! empty($systemSettings->school_name))
                <p class="mt-2 text-xs text-slate-500">School: {{ $systemSettings->school_name }}</p>
            @endif
            @if(! empty($systemSettings->address))
                <p class="text-xs text-slate-500">{{ $systemSettings->address }}</p>
            @endif
            @if(! empty($systemSettings->contact_info))
                <p class="text-xs text-slate-500">{{ $systemSettings->contact_info }}</p>
            @endif
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
