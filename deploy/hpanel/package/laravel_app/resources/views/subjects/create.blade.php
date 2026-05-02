<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Subject</h2></x-slot>
<div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8"><form method="POST" action="{{ route('subjects.store') }}" class="bg-white p-6 rounded shadow-sm grid gap-4">@csrf
<select name="school_class_id" class="border rounded p-2" required><option value="">Select class</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->class_name }}</option>@endforeach</select>
<input name="subject_name" class="border rounded p-2" placeholder="Subject name" required>
<button class="bg-blue-600 text-white px-4 py-2 rounded w-fit">Save</button></form></div></x-app-layout>
