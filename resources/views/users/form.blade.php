<div class="grid gap-4 md:grid-cols-2">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name ?? '')" required />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email ?? '')" required />
    </div>
    <div>
        <x-input-label for="role" value="Role" />
        <select id="role" name="role" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            <option value="admin" @selected(old('role', $user->role ?? 'user') === 'admin')>Admin</option>
            <option value="user" @selected(old('role', $user->role ?? 'user') === 'user')>User</option>
        </select>
    </div>
    <div>
        <x-input-label for="password" value="{{ isset($user) ? 'New Password (optional)' : 'Password' }}" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="!isset($user)" />
    </div>
    <div class="md:col-span-2">
        <x-input-label for="password_confirmation" value="Confirm Password" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="!isset($user)" />
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
</div>
