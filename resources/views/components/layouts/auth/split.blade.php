<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-r dark:border-neutral-800">
                <div class="absolute inset-0 bg-(--color-accent)"></div>
                <div class="relative z-10 flex-1 flex items-center justify-center">
                    <div class="flex flex-col justify-center items-center space-y-4">
                        <div class="flex justify-center">
                            <img src="{{ asset('images/medicine-kit.png') }}" alt="Medical Kit" width="600" height="600">
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                   <div class="z-20 flex flex-col items-center justify-center">
                        <a href="{{ route('home') }}" class="font-medium" wire:navigate>
                            <span class="flex h-15 w-40 items-center justify-center mr-8 rounded-md">
                                <x-app-logo-icon class="size-40 fill-current text-black dark:text-white" />
                            </span>

                            <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                        </a>
                   </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
