<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Class</h2></x-slot>
<div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8"><form method="POST" action="{{ route('classes.store') }}" class="bg-white p-6 rounded shadow-sm grid gap-4">@csrf
<input name="class_name" value="{{ old('class_name') }}" class="border rounded p-2" placeholder="Class name" required>
<input name="classroom" value="{{ old('classroom') }}" class="border rounded p-2" placeholder="Classroom">
<button class="bg-blue-600 text-white px-4 py-2 rounded w-fit">Save</button></form></div></x-app-layout>
