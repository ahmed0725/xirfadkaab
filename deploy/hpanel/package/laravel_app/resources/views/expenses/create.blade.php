<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Add expense</h2></x-slot>
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('expenses.store') }}" class="card grid gap-4">
            @csrf
            @include('expenses.form')
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Save</button>
                <a href="{{ route('expenses.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
