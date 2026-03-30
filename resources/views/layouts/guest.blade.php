<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials.seo', ['seo' => $seo ?? []])
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|newsreader:400,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="atlas-body text-slate-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-8">
            <div class="text-center">
                <a href="/">
                    <x-application-logo class="mx-auto h-20 w-20" />
                </a>
                <p class="mt-4 atlas-eyebrow">Operator access</p>
                <h1 class="font-display text-3xl font-semibold text-slate-950">Peshawar Civic GIS Atlas</h1>
            </div>

            <div class="atlas-panel mt-8 w-full overflow-hidden px-6 py-6 sm:max-w-md sm:px-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
