<div>
    <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" x-cloak @click="sidebarOpen = false"></div>
    <aside class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-slate-200 bg-white transition-all duration-200 lg:z-30"
        :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:w-20' : 'lg:w-72', 'lg:translate-x-0']">
        <div class="flex h-16 items-center justify-between border-b border-slate-200 px-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden">
                <x-application-logo class="h-8 w-auto fill-current text-blue-700" />
                <span class="text-sm font-bold text-slate-800" x-show="!sidebarCollapsed">{{ $systemSettings->school_name ?? 'Xirfad Kaab' }}</span>
            </a>
            <button class="rounded-md p-1 text-slate-500 lg:hidden" @click="sidebarOpen = false" type="button">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="px-3 py-4">
            <button class="hidden w-full items-center justify-center rounded-lg border border-slate-200 py-2 text-slate-600 hover:bg-slate-100 lg:flex" type="button" @click="sidebarCollapsed = !sidebarCollapsed">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
        </div>
        <nav class="flex-1 space-y-1 px-3 pb-4">
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : '' }}"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 3l9 6.75V20a1 1 0 01-1 1h-5.25v-6.75h-5.5V21H4a1 1 0 01-1-1V9.75z"/></svg><span x-show="!sidebarCollapsed">Dashboard</span></a>
            <a href="{{ route('students.index') }}" class="sidebar-link {{ request()->routeIs('students.*') ? 'sidebar-link-active' : '' }}"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 14a4 4 0 10-8 0m8 0a6 6 0 016 6v1H2v-1a6 6 0 016-6m8 0a4 4 0 10-8 0"/></svg><span x-show="!sidebarCollapsed">Students</span></a>
            <a href="{{ route('classes.index') }}" class="sidebar-link {{ request()->routeIs('classes.*') ? 'sidebar-link-active' : '' }}"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7z"/></svg><span x-show="!sidebarCollapsed">Classes</span></a>
            <a href="{{ route('subjects.index') }}" class="sidebar-link {{ request()->routeIs('subjects.*') ? 'sidebar-link-active' : '' }}"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg><span x-show="!sidebarCollapsed">Subjects</span></a>
            <a href="{{ route('attendance.index') }}" class="sidebar-link {{ request()->routeIs('attendance.*') ? 'sidebar-link-active' : '' }}"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 2v4m8-4v4M3 10h18M5 22h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><span x-show="!sidebarCollapsed">Attendance</span></a>
            @if(in_array(auth()->user()->role, ['admin', 'user', 'teacher'], true))
                <a href="{{ route('exams.index') }}" class="sidebar-link {{ request()->routeIs('exams.*') ? 'sidebar-link-active' : '' }}"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg><span x-show="!sidebarCollapsed">Exams</span></a>
            @endif
            <a href="{{ route('fees.index') }}" class="sidebar-link {{ request()->routeIs('fees.*') ? 'sidebar-link-active' : '' }}"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 .896-4 2s1.79 2 4 2 4 .896 4 2-1.79 2-4 2m0-10v12m9-6a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span x-show="!sidebarCollapsed">Fees</span></a>
            @if(in_array(auth()->user()->role, ['admin', 'user'], true))
                <a href="{{ route('expenses.index') }}" class="sidebar-link {{ request()->routeIs('expenses.*') ? 'sidebar-link-active' : '' }}"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg><span x-show="!sidebarCollapsed">Expenses</span></a>
                <a href="{{ route('inventory-items.index') }}" class="sidebar-link {{ request()->routeIs('inventory-items.*') ? 'sidebar-link-active' : '' }}"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span x-show="!sidebarCollapsed">Inventory</span></a>
            @endif
            @if(auth()->user()?->role === 'admin')
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'sidebar-link-active' : '' }}"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 14a4 4 0 10-8 0m8 0a6 6 0 016 6v1H2v-1a6 6 0 016-6m8 0a4 4 0 10-8 0"/></svg><span x-show="!sidebarCollapsed">Users</span></a>
                <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'sidebar-link-active' : '' }}"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg><span x-show="!sidebarCollapsed">Reports</span></a>
                <a href="{{ route('settings.edit') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'sidebar-link-active' : '' }}"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.4 15a1.9 1.9 0 00.4 2.1l.1.1a2.3 2.3 0 01-1.6 3.9 2.2 2.2 0 01-1.6-.7l-.1-.1a1.9 1.9 0 00-2.1-.4 1.9 1.9 0 00-1.1 1.7V22a2.3 2.3 0 01-4.6 0v-.1a1.9 1.9 0 00-1.1-1.7 1.9 1.9 0 00-2.1.4l-.1.1a2.2 2.2 0 01-1.6.7 2.3 2.3 0 01-1.6-3.9l.1-.1a1.9 1.9 0 00.4-2.1 1.9 1.9 0 00-1.7-1.1H2a2.3 2.3 0 010-4.6h.1a1.9 1.9 0 001.7-1.1 1.9 1.9 0 00-.4-2.1l-.1-.1A2.3 2.3 0 014 3.2a2.2 2.2 0 011.6.7l.1.1a1.9 1.9 0 002.1.4 1.9 1.9 0 001.1-1.7V2a2.3 2.3 0 014.6 0v.1a1.9 1.9 0 001.1 1.7 1.9 1.9 0 002.1-.4l.1-.1a2.2 2.2 0 011.6-.7 2.3 2.3 0 011.6 3.9l-.1.1a1.9 1.9 0 00-.4 2.1 1.9 1.9 0 001.7 1.1H22a2.3 2.3 0 010 4.6h-.1a1.9 1.9 0 00-1.7 1.1z"/></svg><span x-show="!sidebarCollapsed">System Settings</span></a>
            @endif
            <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'sidebar-link-active' : '' }}"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 3h1.5A2.25 2.25 0 0115 5.25V6h1.5A2.25 2.25 0 0118.75 8.25v7.5A2.25 2.25 0 0116.5 18h-9A2.25 2.25 0 015.25 15.75v-7.5A2.25 2.25 0 017.5 6H9v-.75A2.25 2.25 0 0111.25 3z"/></svg><span x-show="!sidebarCollapsed">Settings</span></a>
        </nav>
        <div class="border-t border-slate-200 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link w-full"><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1m0-10V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2v-1"/></svg><span x-show="!sidebarCollapsed">Log Out</span></button>
            </form>
        </div>
    </aside>
</div>
