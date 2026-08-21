<nav x-data="{ open: false }" class="relative z-40">
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-950/50 lg:hidden" @click="open = false"></div>

    <aside :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed inset-y-0 left-0 flex w-72 -translate-x-full flex-col border-r border-slate-800 bg-slate-950 text-white transition-transform duration-300 lg:translate-x-0">
        <div class="flex h-20 items-center border-b border-white/10 px-6">
            <a href="{{ route('portal') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white"><img src="{{ asset('images/isg_logo.png') }}" alt="ISG Logo" class="h-7 w-auto"></span>
                <span><span class="block text-sm font-bold tracking-wide">ISG MONITORING</span><span class="block text-[10px] uppercase tracking-[0.2em] text-slate-500">Operations portal</span></span>
            </a>
            <button type="button" @click="open = false" class="ml-auto text-slate-400 hover:text-white lg:hidden" title="Close menu" aria-label="Close menu">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-7">
            <p class="px-3 text-[10px] font-bold uppercase tracking-[0.25em] text-slate-500">Monitoring sites</p>
            <div class="mt-3 space-y-1">
                <a href="{{ route('portal') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('portal') ? 'bg-cyan-400/15 text-cyan-300' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span>▦</span> Portal</a>
                @if(!auth()->user()->isViewer())
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-cyan-400/15 text-cyan-300' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span>⌁</span> VAPT Dashboard</a>
                    <a href="{{ route('vapt.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('vapt.*') ? 'bg-cyan-400/15 text-cyan-300' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span>▤</span> Monitored Systems</a>
                @else
                    <a href="{{ route('viewer.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('viewer.*') ? 'bg-cyan-400/15 text-cyan-300' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span>◉</span> Viewer Portal</a>
                @endif
                <a href="{{ route('calendar') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('calendar') ? 'bg-amber-400/15 text-amber-300' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span>□</span> Calendar</a>
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('register') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('register') ? 'bg-cyan-400/15 text-cyan-300' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span>＋</span> User Management</a>
                @endif
            </div>
        </div>

        <div class="border-t border-white/10 p-4">
            <a href="{{ route('profile.edit') }}" class="mb-3 flex items-center gap-3 rounded-xl px-3 py-3 transition hover:bg-white/10">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-cyan-400/15 text-cyan-300">◉</span>
                <span class="min-w-0"><span class="block truncate text-sm font-semibold">{{ Auth::user()->username }}</span><span class="block text-[10px] uppercase tracking-wider text-slate-500">{{ Auth::user()->role }}</span></span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-slate-400 transition hover:bg-red-400/10 hover:text-red-300"><span>↪</span> Sign out</button>
            </form>
        </div>
    </aside>

    <div class="flex min-h-20 items-center border-b border-slate-200 bg-white px-4 lg:hidden">
        <button type="button" @click="open = true" class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100" title="Open menu" aria-label="Open menu"><span class="text-xl">☰</span></button>
        <span class="ml-3 text-sm font-bold tracking-wide text-slate-800">ISG MONITORING</span>
    </div>
</nav>
