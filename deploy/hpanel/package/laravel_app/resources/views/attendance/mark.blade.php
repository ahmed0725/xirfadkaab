<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mark Daily Attendance</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="markAttendance()" x-init="init()">
        <form method="POST" action="{{ route('attendance.store') }}" class="bg-white p-6 rounded shadow-sm space-y-4">
            @csrf

            <div class="grid md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Date</label>
                    <input type="date" name="date" x-model="date" class="border rounded p-2 w-full" required>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Class</label>
                    <select name="school_class_id" x-model="schoolClassId" @change="loadClass()" class="border rounded p-2 w-full" required>
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Subject (optional)</label>
                    <select name="subject_id" x-model="subjectId" class="border rounded p-2 w-full" :disabled="!schoolClassId">
                        <option value="">—</option>
                        <template x-for="sub in subjects" :key="sub.id">
                            <option :value="sub.id" x-text="sub.subject_name"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <p class="text-sm text-gray-500">Choose a class to load its students. Only one class is marked per submission.</p>
                <button type="button" class="text-sm text-indigo-600 underline disabled:opacity-40" :disabled="!students.length" @click="markAllPresent">Mark all present</button>
            </div>

            <template x-if="loading">
                <p class="text-sm text-slate-500">Loading students…</p>
            </template>

            <template x-if="errorMsg">
                <p class="text-sm text-rose-600" x-text="errorMsg"></p>
            </template>

            <div class="border rounded p-4 space-y-2" x-show="students.length && !loading">
                <h3 class="font-semibold mb-2" x-text="classTitle"></h3>
                <template x-for="(student, index) in students" :key="student.id">
                    <div class="grid md:grid-cols-4 gap-2 mb-2 items-center">
                        <input type="hidden" :name="'records[' + index + '][student_id]'" :value="student.id">

                        <span x-text="student.name + ' (' + student.student_id + ')'"></span>

                        <select :name="'records[' + index + '][status]'" class="border rounded p-2" x-model="student.status">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                        </select>

                        <input :name="'records[' + index + '][note]'" class="border rounded p-2 md:col-span-2" placeholder="Note" x-model="student.note">
                    </div>
                </template>
            </div>

            <p class="text-sm text-amber-700" x-show="schoolClassId && !students.length && !loading && !errorMsg">No students in this class.</p>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-40" :disabled="!schoolClassId || !students.length">Save Attendance</button>
        </form>
    </div>

    <script>
        function markAttendance() {
            return {
                date: '{{ now()->format('Y-m-d') }}',
                schoolClassId: '',
                subjectId: '',
                students: [],
                subjects: [],
                classTitle: '',
                loading: false,
                errorMsg: '',
                csrf: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                async init() {
                    if (this.schoolClassId) await this.loadClass();
                },
                async loadClass() {
                    this.errorMsg = '';
                    this.subjectId = '';
                    this.students = [];
                    this.subjects = [];
                    this.classTitle = '';
                    if (!this.schoolClassId) return;
                    this.loading = true;
                    try {
                        const url = new URL('{{ route('attendance.class-data') }}', window.location.origin);
                        url.searchParams.set('school_class_id', this.schoolClassId);
                        const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        if (res.status === 403 || res.status === 401) {
                            this.errorMsg = 'You are not allowed to take attendance for this class.';
                            return;
                        }
                        if (!res.ok) {
                            this.errorMsg = 'Could not load class data.';
                            return;
                        }
                        const data = await res.json();
                        this.classTitle = data.class.display_name;
                        this.subjects = data.subjects;
                        this.students = data.students.map(s => ({
                            ...s,
                            status: 'present',
                            note: ''
                        }));
                    } catch (e) {
                        this.errorMsg = 'Could not load class data.';
                    } finally {
                        this.loading = false;
                    }
                },
                markAllPresent() {
                    this.students.forEach(s => { s.status = 'present'; });
                }
            };
        }
    </script>
</x-app-layout>
