<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Student</h2></x-slot>
    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('students.update', $student) }}" class="bg-white p-6 rounded shadow-sm grid md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')
            @include('students.form', ['student' => $student])
        </form>
    </div>
</x-app-layout>
