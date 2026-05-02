<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Student Profile</h2></x-slot>
    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded shadow-sm">
            <h3 class="text-xl font-semibold">{{ $student->name }} ({{ $student->student_id }})</h3>
            <p class="text-gray-600 mt-1">Class: {{ $student->schoolClass->class_name }} | Status: {{ $student->status }}</p>
            <p class="text-gray-600">Mother: {{ $student->mother_name }} | Phone: {{ $student->phone }}</p>
            <p class="text-gray-600">Age/Gender: {{ $student->age }} / {{ ucfirst($student->gender) }}</p>
            <p class="text-gray-600">Registration Date: {{ $student->registration_date->format('Y-m-d') }}</p>
        </div>
    </div>
</x-app-layout>
