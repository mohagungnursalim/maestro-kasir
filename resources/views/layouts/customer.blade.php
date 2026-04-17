<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pesan Sekarang – {{ config('app.name') }}</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- FontAwesome --}}
    <script src="https://kit.fontawesome.com/e45dd697a1.js" crossorigin="anonymous"></script>

    @livewireStyles()

    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
        }

        /* Floating cart bar */
        .cart-bar {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Card produk */
        .product-card {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .product-card:active {
            transform: scale(0.97);
        }

        /* Kategori pill */
        .sku-pill {
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        /* Animasi masuk */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-up {
            animation: slideUp 0.35s ease forwards;
        }

        /* Scrollbar tipis */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
    </style>
</head>
<body class="antialiased">
    {{ $slot }}

    @livewireScripts()

    <script>
        // Prevent double-tap zoom on iOS
        document.addEventListener('dblclick', e => e.preventDefault(), { passive: false });
    </script>
</body>
</html>
