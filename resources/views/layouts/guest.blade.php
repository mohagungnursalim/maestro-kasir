<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <script src="https://kit.fontawesome.com/e45dd697a1.js" crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-turnstile.scripts />

    <style>
        .powered-by {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 15px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #777;
        }

        .powered-by p {
            margin: 0;
        }

        .powered-by a {
            color: #444;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 1px dashed #aaa;
            transition: all 0.2s ease-in-out;
        }

        .powered-by a:hover {
            color: #000;
            border-bottom: 1px solid #444;
            text-shadow: 0 0 1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100 px-4">
        <div class="text-center mt-8">
            <h1 class="text-3xl font-bold text-gray-800">Login</h1>
            <p class="text-gray-500 text-sm mt-1">Silakan masuk ke akun Anda</p>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>

        <div class="powered-by">
            <p>Powered by <a href="" target="_blank">Maestro-Kasir</a></p>
        </div>
    </div>
</body>


</html>
