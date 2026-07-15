<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    @vite(['resources/css/app.css'])
    <style>
        @page { margin: 12mm; }
        @page {
            margin: 12mm;
            @top-left { content: ""; }
            @top-right { content: ""; }
            @bottom-left { content: ""; }
            @bottom-right { content: ""; }
        }
        body { padding: 0 !important; }
        table { border-collapse: collapse; }
    </style>
</head>
<body class="bg-white text-slate-800 print:bg-white">
    <div class="mx-auto max-w-5xl space-y-4 px-2 sm:px-0">
        <div class="border-b pb-3">
            <div class="flex items-center gap-3">
                <x-application-logo class="h-12 w-auto" />
                <h1 class="text-2xl font-bold">{{ $systemSettings->school_name ?? 'Xirfad Kaab' }} - Reports</h1>
            </div>
            @if(! empty($systemSettings->address))
                <p class="text-sm text-slate-600">{{ $systemSettings->address }}</p>
            @endif
            @if(! empty($systemSettings->contact_info))
                <p class="text-sm text-slate-600">{{ $systemSettings->contact_info }}</p>
            @endif
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

    </div>
    <script>window.print();</script>
</body>
</html>
