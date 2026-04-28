<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Class</h2></x-slot>
<div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8"><form method="POST" action="{{ route('classes.update',$schoolClass) }}" class="bg-white p-6 rounded shadow-sm grid gap-4">@csrf @method('PUT')
<input name="class_name" value="{{ old('class_name',$schoolClass->class_name) }}" class="border rounded p-2" required>
<input name="classroom" value="{{ old('classroom',$schoolClass->classroom) }}" class="border rounded p-2">
<button class="bg-blue-600 text-white px-4 py-2 rounded w-fit">Update</button></form></div></x-app-layout>
