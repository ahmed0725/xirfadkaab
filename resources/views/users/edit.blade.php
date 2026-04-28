<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Edit User</h2>
    </x-slot>

    <div class="mx-auto max-w-4xl">
        <form method="POST" action="{{ route('users.update', $user) }}" class="card">
            @csrf
            @method('PUT')
            @include('users.form', ['submitLabel' => 'Update User', 'user' => $user])
        </form>
    </div>
</x-app-layout>
