<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8" />

        <meta name="application-name" content="{{ config('app.name') }}" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>{{ config('app.name') }}</title>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        @filamentStyles
        @vite('resources/js/app.js')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
            integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
    </head>

    <body class="antialiased">
        <x-layouts.navbar />

        <main class="min-h-screen bg-gray-50">
            <div class="container mx-auto px-4 py-8">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-full sm:max-w-md">
                        <div class="bg-white shadow-md rounded-lg px-6 py-8">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <x-layouts.footer />

        @livewire('notifications')

        @filamentScripts

    </body>

</html>
