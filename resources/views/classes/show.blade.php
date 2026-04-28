<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Class Details</h2></x-slot>
<div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 grid md:grid-cols-2 gap-4">
<div class="bg-white p-6 rounded shadow-sm"><h3 class="font-semibold">{{ $schoolClass->class_name }}</h3><p class="text-gray-600">Classroom: {{ $schoolClass->classroom ?: '-' }}</p>
<h4 class="mt-4 font-medium">Subjects</h4><ul class="list-disc ms-5">@foreach($schoolClass->subjects as $subject)<li>{{ $subject->subject_name }}</li>@endforeach</ul></div>
<div class="bg-white p-6 rounded shadow-sm"><h4 class="font-medium">Students</h4><ul class="list-disc ms-5">@foreach($schoolClass->students as $student)<li>{{ $student->name }} ({{ $student->student_id }})</li>@endforeach</ul></div>
</div></x-app-layout>
