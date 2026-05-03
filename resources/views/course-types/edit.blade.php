<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Edit course type</h2></x-slot>
    <div class="mx-auto max-w-xl">
        <form method="POST" action="{{ route('course-types.update', $courseType) }}" class="card grid gap-4">
            @csrf
            @method('PUT')
            <div>
                <x-input-label for="name" value="Name" />
                <input id="name" name="name" value="{{ old('name', $courseType->name) }}" class="form-control mt-1" required maxlength="255">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Update</button>
                <a href="{{ route('course-types.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
