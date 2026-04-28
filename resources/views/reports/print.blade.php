<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Print</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white p-8 text-slate-800 print:bg-white">
    <div class="mx-auto max-w-5xl space-y-4">
        <div class="border-b pb-3">
            <h1 class="text-2xl font-bold">Xirfad Kaab Schools - Reports</h1>
            <p class="text-sm text-slate-600">Report: {{ $selectedReportLabel }}</p>
            <p class="text-sm text-slate-600">Period: {{ $periodLabel }}</p>
            <p class="text-sm text-slate-600">Printed at {{ now()->format('Y-m-d H:i') }}</p>
        </div>

        <div class="card">
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
                            <tr class="border-t border-slate-100">
                                @foreach($row as $cell)
                                    <td class="p-3">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td class="p-3 text-slate-500" colspan="{{ count($reportTable['columns']) }}">No records for selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h3 class="mb-2 font-semibold">Summary</h3>
            <ul class="space-y-1 text-sm">
                @foreach($reportSummary as $key => $value)
                    <li class="flex justify-between"><span>{{ $key }}</span><strong>{{ $value }}</strong></li>
                @endforeach
            </ul>
        </div>

        <div class="print:hidden">
            <button class="btn-primary" onclick="window.print()">Print</button>
        </div>
    </div>
</body>
</html>
