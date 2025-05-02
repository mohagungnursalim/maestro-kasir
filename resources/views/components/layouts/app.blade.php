<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Styles & Scripts -->
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js',
    ])
    
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.16.1/sweetalert2.css" integrity="sha512-fjO3Vy3QodX9c6G9AUmr6WuIaEPdGRxBjD7gjatG5gGylzYyrEq3U0q+smkG6CwIY0L8XALRFHh4KPHig0Q1ug==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.16.1/sweetalert2.all.js" integrity="sha512-gG9iGmJWEX+MifPgfm3hFV15M3DdHjyPKYBBd62u/J1E1kGGrdczt3HmQJ66vgM0ytcF9r7cTlq7SbtJLEUWZw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @livewireScripts()
    @livewireStyles()
</head>

<nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            {{-- Logo Section --}}
            <div class="flex items-center justify-start rtl:justify-end">
            {{-- Tombol Toggle & Close Sidebar (Satu Tombol Saja)  --}}
            <button data-drawer-target="drawer-navigation" data-drawer-toggle="drawer-navigation" aria-controls="drawer-navigation"
                type="button"
                class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                <span class="sr-only">Toggle sidebar</span>
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path clip-rule="evenodd" fill-rule="evenodd"
                        d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z">
                    </path>
                </svg>
            </button>
                
                <a href="/dashboard" class="flex ms-2 md:me-24">
                    <img src="{{ asset($settings->store_logo ?? '/logo/default.png') }}" class="h-8 me-3" alt="FlowBite Logo" />
                    <span class="self-center text-xl font-serif font-semibold sm:text-2xl whitespace-nowrap tracking-wide italic">
                        {{ $settings->store_name ?? 'Nama Toko' }}
                    </span>
                </a>                
            </div>

            {{-- Navbar Section --}}
            <x-navbar />
        </div>
    </div>
</nav>

{{-- Sidebar Section --}}
<x-sidebar />

<div class="p-4 ">
    <div class="p-4 border-2 border-gray-200 border rounded-lg mt-14">
        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
</div>
