<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ISG Monitoring System') }} - Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8">
            
            <!-- LOGO / HEADER BRANDING -->
            <div class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white-600 text-white shadow-lg shadow-indigo-200 mb-4">
                    <img src="{{ asset('images/isg_logo.png') }}" alt="ISG Logo" class="block h-9 w-auto">
                </div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">ISG Monitoring System</h1>
                <p class="text-sm text-gray-500 mt-1">Sign in to access system status dashboard</p>
            </div>

            <!-- LOGIN CARD -->
            <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                
                <!-- Session Status / Flash Messages -->
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded-lg border border-green-100">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- USERNAME INPUT -->
                    <div>
                        <label for="username" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Serial Number</label>
                        <div class="relative">
                            <input id="username" 
                                   type="text" 
                                   name="username" 
                                   value="{{ old('username') }}" 
                                   required 
                                   autofocus 
                                   placeholder="Enter your serial number"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition @error('username') border-red-500 @enderror" />
                        </div>
                        @error('username')
                            <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PASSWORD INPUT -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">Password</label>
                            @if (Route::has('password.request'))
                                <a class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition" href="{{ route('password.request') }}">
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               placeholder="••••••••"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition @error('password') border-red-500 @enderror" />
                        @error('password')
                            <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- REMEMBER ME CHECKBOX -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ms-2 text-xs text-gray-600 font-medium">Remember me</span>
                        </label>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <button type="submit" class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-sm rounded-xl shadow-md shadow-indigo-100 transition duration-150 ease-in-out">
                        Sign In
                    </button>
                </form>
            </div>

            <!-- FOOTER -->
            <div class="mt-8 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} VAPT Status Monitoring. All rights reserved.
            </div>

        </div>
    </body>
</html>