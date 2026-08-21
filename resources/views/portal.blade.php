<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'ISG Monitoring System') }} - Portal</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-white antialiased">
        <div id="page-loader" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/65 backdrop-blur-sm" role="status" aria-live="polite" aria-label="Loading">
            <div class="flex flex-col items-center gap-5 text-cyan-200">
                <div class="relative flex h-[5.5rem] w-[5.5rem] items-center justify-center">
                    <span class="cyber-loader-ring"></span>
                    <span class="cyber-loader-core"></span>
                </div>
                <span class="cyber-loader-label text-[10px] font-bold uppercase">Loading module</span>
            </div>
        </div>
        <div class="relative min-h-screen overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(14,165,233,0.18),_transparent_35%),radial-gradient(circle_at_bottom_left,_rgba(99,102,241,0.2),_transparent_35%)]"></div>

            <main class="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col px-6 py-8 sm:px-10 lg:px-12">
                <nav class="flex items-center justify-between border-b border-white/10 pb-6">
                    <a href="{{ route('portal') }}" class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white shadow-lg shadow-cyan-950/30">
                            <img src="{{ asset('images/isg_logo.png') }}" alt="ISG Logo" class="block h-8 w-auto">
                        </span>
                        <span>
                            <span class="block text-sm font-bold tracking-wide text-white">ISG MONITORING</span>
                            <span class="block text-[10px] uppercase tracking-[0.25em] text-slate-400">Operations portal</span>
                        </span>
                    </a>
                    <div class="flex items-center gap-4 text-right">
                        <div class="hidden sm:block">
                            <p class="text-xs font-semibold text-white">{{ auth()->user()->username }}</p>
                            <p class="text-[10px] uppercase tracking-wider text-slate-400">{{ auth()->user()->role }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Sign out" aria-label="Sign out" class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/15 text-slate-300 transition hover:border-white/30 hover:bg-white/10 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                            </button>
                        </form>
                    </div>
                </nav>

                <section class="flex flex-1 flex-col justify-center py-16 sm:py-20">
                    <div class="max-w-2xl">
                        <p class="mb-4 text-xs font-bold uppercase tracking-[0.3em] text-cyan-300">Monitoring command center</p>
                        <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl">Choose a monitoring site.</h1>
                        <p class="mt-5 max-w-xl text-base leading-7 text-slate-300">Select the workspace you want to open. Your access level determines the tools available inside each site.</p>
                    </div>

                    <div class="mt-12 grid gap-5 md:grid-cols-2">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('vapt.index') }}" class="group relative overflow-hidden rounded-3xl border border-white/10 bg-white/[0.07] p-7 transition duration-300 hover:-translate-y-1 hover:border-cyan-300/50 hover:bg-white/[0.11]">
                                <div class="flex items-start justify-between">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400/15 text-cyan-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="m7 16 4-5 3 3 5-7"/></svg>
                                    </span>
                                    <svg class="text-slate-500 transition group-hover:translate-x-1 group-hover:text-cyan-300" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </div>
                                <h2 class="mt-8 text-xl font-bold text-white">VAPT Systems</h2>
                                <p class="mt-2 text-sm leading-6 text-slate-400">Review monitored systems, patching status, personnel, and assessment records.</p>
                                <span class="mt-6 inline-flex rounded-full bg-cyan-400/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-cyan-300">System monitoring</span>
                            </a>
                        @else
                            <a href="{{ route('viewer.dashboard') }}" class="group relative overflow-hidden rounded-3xl border border-white/10 bg-white/[0.07] p-7 transition duration-300 hover:-translate-y-1 hover:border-cyan-300/50 hover:bg-white/[0.11]">
                                <div class="flex items-start justify-between">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400/15 text-cyan-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="m7 16 4-5 3 3 5-7"/></svg>
                                    </span>
                                    <svg class="text-slate-500 transition group-hover:translate-x-1 group-hover:text-cyan-300" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </div>
                                <h2 class="mt-8 text-xl font-bold text-white">VAPT Systems</h2>
                                <p class="mt-2 text-sm leading-6 text-slate-400">View monitored systems, current status, and assessment records.</p>
                                <span class="mt-6 inline-flex rounded-full bg-cyan-400/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-cyan-300">Read-only monitoring</span>
                            </a>
                        @endif

                        <a href="{{ route('calendar') }}" class="group relative overflow-hidden rounded-3xl border border-white/10 bg-white/[0.07] p-7 transition duration-300 hover:-translate-y-1 hover:border-amber-300/50 hover:bg-white/[0.11]">
                            <div class="flex items-start justify-between">
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-400/15 text-amber-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/></svg>
                                </span>
                                <svg class="text-slate-500 transition group-hover:translate-x-1 group-hover:text-amber-300" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </div>
                            <h2 class="mt-8 text-xl font-bold text-white">Activity Calendar</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-400">Coordinate conferences, dispatches, missions, and TIAC activities.</p>
                            <span class="mt-6 inline-flex rounded-full bg-amber-400/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-300">Schedule monitoring</span>
                        </a>
                    </div>
                </section>

                <footer class="border-t border-white/10 pt-5 text-xs text-slate-500">&copy; {{ date('Y') }} VAPT Status Monitoring</footer>
            </main>
        </div>
    </body>
</html>
