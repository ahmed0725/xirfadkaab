<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Users Management</h2>
    </x-slot>

    <div class="space-y-4">
        <div>
            <a href="{{ route('users.create') }}" class="btn-primary">Create User</a>
        </div>

        <div class="table-shell overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-slate-600">
                        <th class="p-3">Name</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Created</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="p-3">{{ $user->name }}</td>
                            <td class="p-3">{{ $user->email }}</td>
                            <td class="p-3">
                                <span class="rounded-full px-2 py-1 text-xs {{ $user->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="p-3">{{ $user->created_at?->format('Y-m-d') }}</td>
                            <td class="p-3">
                                <a href="{{ route('users.edit', $user) }}" class="text-indigo-600">Edit</a>
                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ml-2 text-rose-600" onclick="return confirm('Delete this user?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
