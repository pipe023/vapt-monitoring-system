<nav x-data="{ open: false }" class="relative z-40">
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-950/50 lg:hidden" @click="open = false"></div>

    <aside :class="[open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0', sidebarCollapsed ? 'sidebar-collapsed' : '']" class="fixed inset-y-0 left-0 flex w-72 -translate-x-full flex-col border-r border-slate-800 bg-slate-950 text-white transition-all duration-300 lg:translate-x-0">
        <div :class="sidebarCollapsed ? 'lg:px-4' : 'lg:px-6'" class="relative flex h-20 items-center border-b border-white/10 px-6">
            <a href="{{ route('portal') }}" class="flex items-center gap-3">
            <span :class="sidebarCollapsed ? 'lg:h-9 lg:w-9' : 'lg:h-10 lg:w-10'" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white"><img src="{{ asset('images/isg_logo.png') }}" alt="ISG Logo" class="h-7 w-auto"></span>
                <span x-show="!sidebarCollapsed" x-transition.opacity class="sidebar-label"><span class="block text-sm font-bold tracking-wide">ISG MONITORING</span><span class="block text-[10px] uppercase tracking-[0.2em] text-slate-500">Operations portal</span></span>
            </a>
            <button type="button" @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed)" class="sidebar-collapse-toggle ml-auto hidden text-slate-400 hover:text-white lg:block" title="Collapse navigation" aria-label="Collapse navigation">
                <svg x-show="!sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m11 17-5-5 5-5"/><path d="m18 17-5-5 5-5"/></svg>
                <svg x-show="sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m13 17 5-5-5-5"/><path d="m6 17 5-5-5-5"/></svg>
            </button>
            <button type="button" @click="open = false" class="ml-auto text-slate-400 hover:text-white lg:hidden" title="Close menu" aria-label="Close menu">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-7">
            <p x-show="!sidebarCollapsed" x-transition.opacity class="sidebar-label px-3 text-[10px] font-bold uppercase tracking-[0.25em] text-slate-500">Monitoring sites</p>
            <div class="mt-3 space-y-1">
                <a href="{{ route('portal') }}" @click="sidebarCollapsed = false; localStorage.setItem('sidebarCollapsed', false)" title="Portal" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('portal') ? 'bg-cyan-400/15 text-cyan-300' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span>▦</span><span x-show="!sidebarCollapsed" class="sidebar-label">Portal</span></a>
                @if(!auth()->user()->isViewer())
                    <a href="{{ route('dashboard') }}" @click="sidebarCollapsed = false; localStorage.setItem('sidebarCollapsed', false)" title="VAPT Dashboard" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-cyan-400/15 text-cyan-300' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span>⌁</span><span x-show="!sidebarCollapsed" class="sidebar-label">VAPT Dashboard</span></a>
                    <a href="{{ route('vapt.index') }}" @click="sidebarCollapsed = false; localStorage.setItem('sidebarCollapsed', false)" title="Monitored Systems" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('vapt.*') ? 'bg-cyan-400/15 text-cyan-300' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span>▤</span><span x-show="!sidebarCollapsed" class="sidebar-label">Monitored Systems</span></a>
                @else
                    <a href="{{ route('viewer.dashboard') }}" @click="sidebarCollapsed = false; localStorage.setItem('sidebarCollapsed', false)" title="Viewer Portal" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('viewer.*') ? 'bg-cyan-400/15 text-cyan-300' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span>◉</span><span x-show="!sidebarCollapsed" class="sidebar-label">Viewer Portal</span></a>
                @endif
                <a href="{{ route('calendar') }}" @click="sidebarCollapsed = false; localStorage.setItem('sidebarCollapsed', false)" title="Calendar" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('calendar') ? 'bg-amber-400/15 text-amber-300' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span>□</span><span x-show="!sidebarCollapsed" class="sidebar-label">Calendar</span></a>
                <a href="{{ route('documents.index') }}" @click="sidebarCollapsed = false; localStorage.setItem('sidebarCollapsed', false)" title="Document Tracking" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('documents.*') ? 'bg-emerald-400/15 text-emerald-300' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span>▧</span><span x-show="!sidebarCollapsed" class="sidebar-label">Document Tracking</span></a>
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('register') }}" @click="sidebarCollapsed = false; localStorage.setItem('sidebarCollapsed', false)" title="User Management" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('register') ? 'bg-cyan-400/15 text-cyan-300' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span>＋</span><span x-show="!sidebarCollapsed" class="sidebar-label">User Management</span></a>
                @endif
            </div>
        </div>

        <div class="border-t border-white/10 p-4">
            <a href="{{ route('profile.edit') }}" title="Profile" class="mb-3 flex items-center gap-3 rounded-xl px-3 py-3 transition hover:bg-white/10">
                <span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-cyan-400/15 text-cyan-300">
                    @if (Auth::user()->profile_photo)
                        <img src="{{ Storage::url(Auth::user()->profile_photo) }}" alt="{{ Auth::user()->username }} profile photo" class="h-full w-full object-cover">
                    @else
                        <span class="text-xs font-bold">{{ strtoupper(substr(Auth::user()->username, 0, 1)) }}</span>
                    @endif
                </span>
                <span x-show="!sidebarCollapsed" x-transition.opacity class="sidebar-label min-w-0"><span class="block truncate text-sm font-semibold">{{ Auth::user()->username }}</span><span class="block text-[10px] uppercase tracking-wider text-slate-500">{{ Auth::user()->role }}</span></span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Sign out" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-slate-400 transition hover:bg-red-400/10 hover:text-red-300"><span>↪</span><span x-show="!sidebarCollapsed" class="sidebar-label">Sign out</span></button>
            </form>
        </div>
    </aside>

    <div class="flex min-h-20 items-center border-b border-slate-200 bg-white px-4 lg:hidden">
        <button type="button" @click="open = true" class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100" title="Open menu" aria-label="Open menu"><span class="text-xl">☰</span></button>
        <a href="{{ route('portal') }}" class="ml-3 flex items-center gap-2">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-950"><img src="{{ asset('images/isg_logo.png') }}" alt="ISG Logo" class="h-6 w-auto"></span>
            <span class="text-sm font-bold tracking-wide text-slate-800">ISG MONITORING</span>
        </a>
    </div>

    <style>
        @media (min-width: 1024px) {
            .sidebar-collapsed {
                width: 5rem !important;
            }

            .sidebar-collapsed nav,
            .sidebar-collapsed .sidebar-label {
                overflow: hidden;
            }

            .sidebar-collapsed .sidebar-label {
                display: none !important;
            }

            .sidebar-collapsed .sidebar-collapse-toggle {
                position: absolute;
                right: 1rem;
                margin-left: 0;
            }

            .sidebar-collapsed .sidebar-collapse-toggle svg {
                display: none;
            }

            .sidebar-collapsed .sidebar-collapse-toggle svg:last-child {
                display: block;
            }

            .sidebar-collapsed .flex.items-center.gap-3.rounded-xl {
                justify-content: center;
                gap: 0;
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }
    </style>
</nav>
