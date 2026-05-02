<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Edit expense</h2></x-slot>
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('expenses.update', $expense) }}" class="card grid gap-4">
            @csrf
            @method('PATCH')
            @include('expenses.form', ['expense' => $expense])
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Update</button>
                <a href="{{ route('expenses.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
