<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ISG Monitoring System') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div id="page-loader" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/65 backdrop-blur-sm" role="status" aria-live="polite" aria-label="Loading">
            <div class="flex flex-col items-center gap-5 text-cyan-200">
                <div class="relative flex h-[5.5rem] w-[5.5rem] items-center justify-center">
                    <span class="cyber-loader-ring"></span>
                    <span class="cyber-loader-core"></span>
                </div>
                <span class="cyber-loader-label text-[10px] font-bold uppercase">Loading module</span>
            </div>
        </div>
        <div class="min-h-screen bg-gray-100 lg:pl-72">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="w-full max-w-[98%] mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
