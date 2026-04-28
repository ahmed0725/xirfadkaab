<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Register Student</h2></x-slot>
    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('students.store') }}" class="card grid md:grid-cols-2 gap-4">
            @csrf
            @include('students.form', ['student' => null])
        </form>
    </div>
</x-app-layout>
