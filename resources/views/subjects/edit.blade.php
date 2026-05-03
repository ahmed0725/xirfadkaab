<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Subject</h2></x-slot>
<div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8"><form method="POST" action="{{ route('subjects.update',$subject) }}" class="bg-white p-6 rounded shadow-sm grid gap-4">@csrf @method('PUT')
<select name="school_class_id" class="border rounded p-2" required>@foreach($classes as $class)<option value="{{ $class->id }}" @selected($class->id==$subject->school_class_id)>{{ $class->display_name }}</option>@endforeach</select>
<input name="subject_name" value="{{ old('subject_name',$subject->subject_name) }}" class="border rounded p-2" required>
<button class="bg-blue-600 text-white px-4 py-2 rounded w-fit">Update</button></form></div></x-app-layout>
