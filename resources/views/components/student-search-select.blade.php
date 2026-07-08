@props([
    'name' => 'student_id',
    'label' => 'Student',
    'selectedId' => null,
    'selectedLabel' => null,
    'required' => true,
    'tuitionOnly' => false,
    'inputClass' => 'form-control mt-1',
])

<div
    x-data="studentSearchSelect({
        searchUrl: @js(route('students.search')),
        tuitionOnly: @js($tuitionOnly),
        selectedId: @js(old($name, $selectedId)),
        selectedLabel: @js(old('_student_label', $selectedLabel)),
    })"
    @click.outside="closeResults()"
    class="relative"
>
    @if($label)
        <x-input-label :for="$name.'_search'" :value="$label" />
    @endif

    <input type="hidden" name="{{ $name }}" x-model="selectedId" @if($required) required @endif>

    <input
        id="{{ $name }}_search"
        type="text"
        x-model="query"
        @input.debounce.300ms="searchStudents()"
        @focus="openResults()"
        @keydown.escape="closeResults()"
        @keydown.arrow-down.prevent="highlightNext()"
        @keydown.arrow-up.prevent="highlightPrevious()"
        @keydown.enter.prevent="selectHighlighted()"
        class="{{ $inputClass }}"
        placeholder="Search by student name or ID"
        autocomplete="off"
    >

    <p x-show="selectedLabel && !query" x-cloak class="mt-1 text-xs text-slate-500">
        Selected: <span x-text="selectedLabel"></span>
        <button type="button" class="ml-1 text-indigo-600 underline" @click="clearSelection()">Change</button>
    </p>

    <ul
        x-show="open && results.length"
        x-cloak
        class="absolute z-30 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg"
    >
        <template x-for="(student, index) in results" :key="student.id">
            <li>
                <button
                    type="button"
                    class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50"
                    :class="{ 'bg-indigo-50': index === highlightedIndex }"
                    @click="selectStudent(student)"
                >
                    <span class="font-medium" x-text="student.name"></span>
                    <span class="text-slate-500" x-text="' (' + student.student_id + ')'"></span>
                    <span class="block text-xs text-slate-500" x-text="student.class_name"></span>
                </button>
            </li>
        </template>
    </ul>

    <p x-show="open && query.length >= 1 && !loading && results.length === 0" x-cloak class="absolute z-30 mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500 shadow-lg">
        No students found.
    </p>

    <p x-show="loading" x-cloak class="mt-1 text-xs text-slate-500">Searching…</p>

    <x-input-error :messages="$errors->get($name)" class="mt-1" />
</div>

<script>
    if (typeof window.studentSearchSelect !== 'function') {
        window.studentSearchSelect = function (config) {
            return {
                searchUrl: config.searchUrl,
                tuitionOnly: config.tuitionOnly,
                selectedId: config.selectedId ? String(config.selectedId) : '',
                selectedLabel: config.selectedLabel || '',
                query: config.selectedLabel || '',
                results: [],
                open: false,
                loading: false,
                highlightedIndex: -1,

                openResults() {
                    this.open = true;
                    if (this.query.length >= 1) {
                        this.searchStudents();
                    }
                },

                closeResults() {
                    this.open = false;
                    this.highlightedIndex = -1;
                },

                async searchStudents() {
                    if (this.query.length < 1) {
                        this.results = [];
                        this.closeResults();
                        return;
                    }

                    this.loading = true;
                    this.open = true;

                    try {
                        const params = new URLSearchParams({
                            q: this.query,
                            tuition_only: this.tuitionOnly ? '1' : '0',
                        });
                        const response = await fetch(`${this.searchUrl}?${params.toString()}`, {
                            headers: { Accept: 'application/json' },
                        });

                        if (!response.ok) {
                            this.results = [];
                            return;
                        }

                        this.results = await response.json();
                        this.highlightedIndex = this.results.length ? 0 : -1;
                    } catch (error) {
                        this.results = [];
                    } finally {
                        this.loading = false;
                    }
                },

                selectStudent(student) {
                    this.selectedId = String(student.id);
                    this.selectedLabel = `${student.name} (${student.student_id}) — ${student.class_name}`;
                    this.query = this.selectedLabel;
                    this.closeResults();
                    this.$dispatch('student-selected', student);
                },

                clearSelection() {
                    this.selectedId = '';
                    this.selectedLabel = '';
                    this.query = '';
                    this.results = [];
                    this.$dispatch('student-cleared');
                },

                highlightNext() {
                    if (!this.results.length) {
                        return;
                    }

                    this.highlightedIndex = (this.highlightedIndex + 1) % this.results.length;
                },

                highlightPrevious() {
                    if (!this.results.length) {
                        return;
                    }

                    this.highlightedIndex = (this.highlightedIndex - 1 + this.results.length) % this.results.length;
                },

                selectHighlighted() {
                    if (this.highlightedIndex >= 0 && this.results[this.highlightedIndex]) {
                        this.selectStudent(this.results[this.highlightedIndex]);
                    }
                },
            };
        };
    }
</script>
