<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Create User</h2>
    </x-slot>

    <div class="mx-auto max-w-4xl">
        <form method="POST" action="{{ route('users.store') }}" class="card">
            @csrf
            @include('users.form', ['submitLabel' => 'Create User'])
        </form>
    </div>
</x-app-layout>
