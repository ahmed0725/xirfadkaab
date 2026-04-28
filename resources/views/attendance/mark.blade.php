<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Mark Daily Attendance</h2></x-slot>
<div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8"><form method="POST" action="{{ route('attendance.store') }}" class="bg-white p-6 rounded shadow-sm space-y-4">@csrf
<div class="grid md:grid-cols-3 gap-3"><input type="date" name="date" value="{{ now()->format('Y-m-d') }}" class="border rounded p-2" required>
<select name="school_class_id" class="border rounded p-2" required><option value="">Class</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->class_name }}</option>@endforeach</select>
<select name="subject_id" class="border rounded p-2"><option value="">Subject (optional)</option>@foreach($classes as $class)@foreach($class->subjects as $subject)<option value="{{ $subject->id }}">{{ $class->class_name }} - {{ $subject->subject_name }}</option>@endforeach@endforeach</select></div>
<p class="text-sm text-gray-500">Set status and note for students, then save.</p>
@foreach($classes as $class)<div class="border rounded p-4"><h3 class="font-semibold mb-2">{{ $class->class_name }}</h3>
@foreach($class->students as $student)<div class="grid md:grid-cols-4 gap-2 mb-2 items-center"><input type="hidden" name="records[{{ $student->id }}][student_id]" value="{{ $student->id }}">
<span>{{ $student->name }}</span><select name="records[{{ $student->id }}][status]" class="border rounded p-2"><option value="present">Present</option><option value="absent">Absent</option><option value="late">Late</option></select>
<input name="records[{{ $student->id }}][note]" class="border rounded p-2 md:col-span-2" placeholder="Note"></div>@endforeach</div>@endforeach
<button class="bg-blue-600 text-white px-4 py-2 rounded">Save Attendance</button></form></div></x-app-layout>
