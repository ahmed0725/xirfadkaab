<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">System Settings</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="card space-y-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <x-input-label for="logo" value="System Logo" />
                    <div class="mt-2 flex items-center gap-4">
                        @if(! empty($settings->logo_path) && file_exists(public_path($settings->logo_path)))
                            <img src="{{ asset($settings->logo_path) }}" alt="Logo" class="h-16 w-auto rounded border border-slate-200 bg-white">
                        @else
                            <div class="h-16 w-16 rounded border border-slate-200 bg-slate-50 flex items-center justify-center text-xs text-slate-500">
                                No logo
                            </div>
                        @endif
                        <div class="flex-1">
                            <input id="logo" name="logo" type="file" class="form-control">
                            <p class="text-xs text-slate-500 mt-1">Upload JPG/PNG/WebP (recommended size under 4MB).</p>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="school_name" value="School Name" />
                    <input id="school_name" name="school_name" type="text" class="form-control mt-1" value="{{ old('school_name', $settings->school_name) }}" required>
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="address" value="Address" />
                    <textarea id="address" name="address" rows="3" class="form-control mt-1" required>{{ old('address', $settings->address ?? '') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="contact_info" value="Contact Info" />
                    <input id="contact_info" name="contact_info" type="text" class="form-control mt-1" value="{{ old('contact_info', $settings->contact_info) }}" required>
                </div>
            </div>

            <div class="mt-6 flex gap-2">
                <button class="btn-primary">Save Settings</button>
                <a href="{{ route('dashboard') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>

