<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Document Tracking Login</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900">
        <div class="flex min-h-screen items-center justify-center px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md rounded-2xl border border-emerald-100 bg-white p-8 shadow-sm">
                <div class="mb-6 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/></svg>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900">Document Tracking Module</h1>
                    <p class="mt-2 text-sm text-slate-500">Sign in to access the records management portal.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('documents.login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="username" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-600">Username</label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="Enter username">
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-600">Password</label>
                        <input id="password" type="password" name="password" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="••••••••">
                    </div>

                    <label for="remember" class="flex items-center gap-2 text-sm text-slate-600">
                        <input id="remember" type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        Remember me
                    </label>

                    <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Sign In to Document Tracking
                    </button>
                </form>
            </div>
        </div>
    </body>
</html>
