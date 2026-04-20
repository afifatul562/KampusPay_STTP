<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo_kampus.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo_kampus.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo_kampus.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased text-fluid-base min-h-[100dvh]">
        <div class="min-h-[100dvh] flex flex-col sm:justify-center items-center pt-6 sm:pt-0 pb-safe px-3 sm:px-4 bg-gray-100 dark:bg-gray-900">
            <div>
                <a href="/" class="inline-flex touch-target rounded-lg">
                    <x-application-logo class="w-16 h-16 sm:w-20 sm:h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-4 sm:mt-6 px-4 sm:px-6 py-5 sm:py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden rounded-xl sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
