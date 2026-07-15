<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Reports Center</h2></x-slot>
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
            @foreach($overviewStats as $label => $stat)
                <a href="{{ $stat['url'] }}" class="metric-card block transition hover:ring-2 hover:ring-blue-300">
                    <p class="text-sm text-slate-500">{{ $label }}</p>
                    <p class="mt-1 text-2xl font-bold text-slate-800">{{ $stat['value'] }}</p>
                    <p class="mt-1 text-xs text-blue-600">View list &rarr;</p>
                </a>
            @endforeach
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($reportGroups as $group => $types)
                <div class="card flex flex-col space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-slate-800">{{ $group }}</h3>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ count($types) }} {{ Str::plural('report', count($types)) }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $groupDescriptions[$group] ?? '' }}</p>
                    </div>
                    <ul class="flex-1 space-y-1 border-t border-slate-100 pt-3">
                        @foreach($types as $type => $label)
                            <li>
                                <a href="{{ route('reports.index', ['report_type' => $type]) }}"
                                   class="flex items-center justify-between rounded-lg px-2 py-1.5 text-sm text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">
                                    <span>{{ $label }}</span>
                                    <span aria-hidden="true" class="text-slate-400">&rarr;</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
