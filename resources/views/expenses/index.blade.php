<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Expenses</h2></x-slot>
    <div class="space-y-6">
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('expenses.create') }}" class="btn-primary">Add expense</a>
        @endif

        <form method="GET" class="card grid gap-3 md:grid-cols-6">
            <select name="month" class="form-control">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected((int)$filters['month'] === $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                @endfor
            </select>
            <input type="number" name="year" value="{{ $filters['year'] }}" class="form-control" min="2000" max="2100">
            <select name="category" class="form-control">
                <option value="">All categories</option>
                @foreach(\App\Models\Expense::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['category'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control">
                <option value="">All statuses</option>
                @foreach(\App\Models\Expense::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Search description / staff">
            <button type="submit" class="btn-primary">Apply</button>
        </form>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="metric-card">
                <p class="text-sm text-slate-500">Total (filtered)</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">${{ number_format($summary['total_amount'], 2) }}</p>
            </div>
            <div class="metric-card">
                <p class="text-sm text-slate-500">Transactions</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ $summary['count'] }}</p>
            </div>
            <div class="card md:col-span-1">
                <p class="text-sm font-semibold text-slate-700">By category</p>
                <ul class="mt-2 space-y-1 text-sm text-slate-600">
                    @forelse($summary['by_category'] as $cat => $row)
                        <li class="flex justify-between gap-2">
                            <span>{{ \App\Models\Expense::CATEGORIES[$cat] ?? $cat }}</span>
                            <span>${{ number_format((float) $row->total, 2) }}</span>
                        </li>
                    @empty
                        <li class="text-slate-400">No data</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="table-shell overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-slate-600">
                        <th class="p-3">Date</th>
                        <th class="p-3">Category</th>
                        <th class="p-3">Amount</th>
                        <th class="p-3">Payment</th>
                        <th class="p-3">Staff</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Description</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="p-3">{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td class="p-3">{{ \App\Models\Expense::CATEGORIES[$expense->category] ?? $expense->category }}</td>
                            <td class="p-3 font-medium">${{ number_format((float) $expense->amount, 2) }}</td>
                            <td class="p-3">{{ \App\Models\Expense::PAYMENT_METHODS[$expense->payment_method] ?? $expense->payment_method }}</td>
                            <td class="p-3">{{ $expense->staff_name ?? '—' }}</td>
                            <td class="p-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $expense->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800' }}">
                                    {{ \App\Models\Expense::STATUSES[$expense->status] ?? $expense->status }}
                                </span>
                            </td>
                            <td class="p-3 max-w-xs truncate">{{ $expense->description ?? '—' }}</td>
                            <td class="p-3 whitespace-nowrap">
                                <a href="{{ route('expenses.show', $expense) }}" class="text-blue-600">View</a>
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('expenses.edit', $expense) }}" class="ml-2 text-indigo-600">Edit</a>
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline" onsubmit="return confirm('Delete this expense?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-2 text-rose-600">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $expenses->links() }}</div>
    </div>
</x-app-layout>
