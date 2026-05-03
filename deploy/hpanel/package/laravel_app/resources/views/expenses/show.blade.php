<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Expense details</h2></x-slot>
    <div class="mx-auto max-w-2xl card space-y-3 text-sm">
        <p><span class="font-semibold text-slate-800">Category:</span> {{ \App\Models\Expense::CATEGORIES[$expense->category] ?? $expense->category }}</p>
        <p><span class="font-semibold text-slate-800">Amount:</span> ${{ number_format((float) $expense->amount, 2) }}</p>
        <p><span class="font-semibold text-slate-800">Date:</span> {{ $expense->expense_date->format('Y-m-d') }}</p>
        <p><span class="font-semibold text-slate-800">Payment method:</span> {{ \App\Models\Expense::PAYMENT_METHODS[$expense->payment_method] ?? $expense->payment_method }}</p>
        @if($expense->staff_name)
            <p><span class="font-semibold text-slate-800">Staff:</span> {{ $expense->staff_name }}</p>
        @endif
        <p><span class="font-semibold text-slate-800">Status:</span> {{ \App\Models\Expense::STATUSES[$expense->status] ?? $expense->status }}</p>
        @if($expense->description)
            <p><span class="font-semibold text-slate-800">Description:</span> {{ $expense->description }}</p>
        @endif
        <div class="flex gap-2 pt-4">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('expenses.edit', $expense) }}" class="btn-primary">Edit</a>
            @endif
            <a href="{{ route('expenses.index') }}" class="btn-secondary">Back to list</a>
        </div>
    </div>
</x-app-layout>
