<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO Meta Tags --}}
    <title>{{ $settings->store_name ?? 'Kedai Mie-Mie' }} - Mie Kering Khas Makassar | Cita Rasa Legendaris di Palu</title>
    <meta name="description" content="Kedai Mie-Mie menghadirkan sajian Mie Kering Mie Awa khas Makassar legendaris di Kota Palu,Cita rasa autentik warisan turun-temurun.">
    <meta name="keywords" content="mie awa, mie kering, mie makassar, kedai mie palu, mie spesial, mie yamin, kuliner palu, mie legendaris, warisan kuliner makassar, kedai mie-mie">
    <meta name="author" content="{{ $settings->store_name ?? 'Kedai Mie-Mie' }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}">

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="{{ $settings->store_name ?? 'Kedai Mie-Mie' }} - Mie Kering Mie Awa Khas Makassar Legendaris">
    <meta property="og:description" content="Sajian istimewa Mie Kering Mie Mie Awa khas Makassar dengan cita rasa autentik warisan turun-temurun, kini hadir di Kota Palu. Mie spesial, yamin, dan mercon level tingkat!">
    <meta property="og:image" content="{{ asset('images/mie-awa.jpg') }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="{{ $settings->store_name ?? 'Kedai Mie-Mie' }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $settings->store_name ?? 'Kedai Mie-Mie' }} - Mie Kering Mie Awa Khas Makassar">
    <meta name="twitter:description" content="Sajian istimewa Mie Kering Mie Awa khas Makassar dengan cita rasa autentik warisan turun-temurun, kini hadir di Kota Palu.">
    <meta name="twitter:image" content="{{ asset('images/mie-awa.jpg') }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Structured Data / JSON-LD --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Restaurant',
        'name' => $settings->store_name ?? 'Kedai Mie-Mie',
        'image' => asset('images/mie-awa.jpg'),
        'url' => url('/'),
        'description' => 'Kedai Mie-Mie menghadirkan sajian Mie Kering Mie Awa khas Makassar legendaris di Kota Palu.',
        'servesCuisine' => 'Indonesian',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Palu',
            'addressCountry' => 'ID',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; overflow-x: hidden; }

        /* ─── Animations ─── */
        @@keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @@keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-40px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @@keyframes fadeInRight {
            from { opacity: 0; transform: translateX(40px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @@keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        @@keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(249, 115, 22, 0.3); }
            50% { box-shadow: 0 0 40px rgba(249, 115, 22, 0.6); }
        }
        @@keyframes blob {
            0%, 100% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
            25% { border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%; }
            50% { border-radius: 50% 60% 30% 60% / 30% 60% 70% 40%; }
            75% { border-radius: 60% 40% 60% 30% / 60% 40% 30% 60%; }
        }
        @@keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        @@keyframes countUp {
            from { opacity: 0; transform: scale(0.5); }
            to { opacity: 1; transform: scale(1); }
        }
        @@keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @@keyframes pulse-badge {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        }
        @@keyframes flip {
            0% { transform: rotateX(0deg); }
            50% { transform: rotateX(-90deg); }
            100% { transform: rotateX(0deg); }
        }
        @@keyframes glow-ring {
            0%, 100% { box-shadow: 0 0 15px rgba(249, 115, 22, 0.2), inset 0 0 15px rgba(249, 115, 22, 0.05); }
            50% { box-shadow: 0 0 30px rgba(249, 115, 22, 0.4), inset 0 0 20px rgba(249, 115, 22, 0.1); }
        }

        /* ─── Coming Soon badge ─── */
        .coming-soon-badge {
            animation: pulse-badge 2s ease-in-out infinite;
        }

        /* ─── Countdown cards ─── */
        .countdown-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(249, 115, 22, 0.15);
            transition: all 0.3s ease;
        }
        .countdown-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 40px -10px rgba(249, 115, 22, 0.2);
            border-color: rgba(249, 115, 22, 0.3);
        }
        .countdown-number {
            background: linear-gradient(135deg, #ea580c, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ─── Ribbon ─── */
        .coming-soon-ribbon {
            position: relative;
            overflow: hidden;
        }
        .coming-soon-ribbon::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 60%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }

        /* ─── Scroll Animation Classes ─── */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .animate-on-scroll.delay-1 { transition-delay: 0.1s; }
        .animate-on-scroll.delay-2 { transition-delay: 0.2s; }
        .animate-on-scroll.delay-3 { transition-delay: 0.3s; }
        .animate-on-scroll.delay-4 { transition-delay: 0.4s; }

        /* ─── Hero floating image ─── */
        .hero-image-float {
            animation: float 6s ease-in-out infinite;
        }
        .hero-blob {
            animation: blob 8s ease-in-out infinite;
        }
        .hero-glow {
            animation: pulse-glow 3s ease-in-out infinite;
        }

        /* ─── Gradient text ─── */
        .gradient-text {
            background: linear-gradient(135deg, #ea580c, #f97316, #fb923c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ─── Card hover effects ─── */
        .menu-card {
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }
        .menu-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(249,115,22,0.05) 0%, rgba(251,146,60,0.1) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .menu-card:hover::before {
            opacity: 1;
        }
        .menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px -15px rgba(249, 115, 22, 0.25);
        }
        .menu-card .icon-box {
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .menu-card:hover .icon-box {
            transform: scale(1.1) rotate(-5deg);
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
        }

        /* ─── Stats counter ─── */
        .stat-item {
            position: relative;
        }
        .stat-item::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 50%;
            width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(249,115,22,0.3), transparent);
        }
        .stat-item:last-child::after {
            display: none;
        }

        /* ─── Shimmer badge ─── */
        .shimmer-badge {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            background-size: 200% 100%;
            animation: shimmer 3s infinite;
        }

        /* ─── Navbar glass effect ─── */
        .nav-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .nav-glass.scrolled {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
        }

        /* ─── Mobile menu ─── */
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
            opacity: 0;
        }
        .mobile-menu.open {
            max-height: 300px;
            opacity: 1;
        }

        /* ─── Particle dots ─── */
        .particle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            animation: float 4s ease-in-out infinite;
        }

        /* ─── CTA gradient border ─── */
        .cta-section {
            position: relative;
        }
        .cta-section::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(135deg, #f97316, #ea580c, #c2410c, #f97316);
            border-radius: 1.5rem;
            z-index: -1;
            opacity: 0.5;
            filter: blur(10px);
        }

        /* ─── Back to top ─── */
        .back-to-top {
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.4s ease;
        }
        .back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* ─── Nav active link ─── */
        .nav-link {
            position: relative;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #f97316, #ea580c);
            transition: all 0.3s ease;
            transform: translateX(-50%);
            border-radius: 999px;
        }
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 70%;
        }

        /* ─── Footer social icons ─── */
        .social-icon {
            width: 40px; height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        .social-icon:hover {
            background: linear-gradient(135deg, #f97316, #ea580c);
            border-color: transparent;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(249,115,22,0.3);
        }
    </style>
</head>
<body class="bg-orange-50 text-gray-800 antialiased selection:bg-orange-500 selection:text-white">

    {{-- ═══════════════ Navbar ═══════════════ --}}
    <nav id="navbar" class="nav-glass fixed w-full z-50 top-0 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 lg:h-20 items-center">
                {{-- Logo --}}
                <a href="#home" class="flex-shrink-0 flex items-center gap-2.5 group">
                    <div class="relative">
                        <img src="{{ asset($settings->store_logo ?? '/logo/default.png') }}" class="h-10 lg:h-11 w-auto rounded-xl shadow-sm transition-transform duration-300 group-hover:scale-105" alt="{{ $settings->store_name ?? 'Kedai Mie-Mie' }} Logo" />
                    </div>
                    <span class="font-extrabold text-xl lg:text-2xl gradient-text tracking-tight">{{ $settings->store_name ?? 'Kedai Mie-Mie' }}</span>
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center space-x-1">
                    <a href="#home" class="nav-link text-gray-600 hover:text-orange-500 px-4 py-2 text-sm font-semibold transition-colors">Beranda</a>
                    <a href="#tentang" class="nav-link text-gray-600 hover:text-orange-500 px-4 py-2 text-sm font-semibold transition-colors">Tentang Kami</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="ml-4 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            Dashboard
                        </a>
                    @endauth
                </div>

                {{-- Mobile Menu Button --}}
                <button id="mobile-menu-btn" type="button" class="md:hidden relative w-10 h-10 flex items-center justify-center rounded-xl bg-orange-50 text-orange-500 hover:bg-orange-100 transition-colors" aria-label="Menu navigasi">
                    <svg id="menu-icon-open" class="w-5 h-5 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <svg id="menu-icon-close" class="w-5 h-5 absolute transition-all duration-300 opacity-0 scale-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Mobile Menu --}}
            <div id="mobile-menu" class="mobile-menu md:hidden">
                <div class="pb-4 pt-2 space-y-1 border-t border-orange-100">
                    <a href="#home" class="block px-4 py-2.5 rounded-xl text-gray-600 hover:text-orange-500 hover:bg-orange-50 text-sm font-semibold transition-all">Beranda</a>
                    <a href="#tentang" class="block px-4 py-2.5 rounded-xl text-gray-600 hover:text-orange-500 hover:bg-orange-50 text-sm font-semibold transition-all">Tentang Kami</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="block mx-4 mt-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white px-4 py-2.5 rounded-xl text-sm font-bold text-center transition-all shadow-md">Dashboard POS</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- ═══════════════ Hero Section ═══════════════ --}}
    <section id="home" class="relative pt-28 pb-20 lg:pt-40 lg:pb-32 overflow-hidden">
        {{-- Background decorative elements --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="particle w-3 h-3 bg-orange-300/40 top-[15%] left-[10%]" style="animation-delay: 0s;"></div>
            <div class="particle w-2 h-2 bg-orange-400/30 top-[25%] right-[15%]" style="animation-delay: 1s;"></div>
            <div class="particle w-4 h-4 bg-orange-200/50 bottom-[20%] left-[20%]" style="animation-delay: 2s;"></div>
            <div class="particle w-2 h-2 bg-orange-300/40 top-[60%] right-[25%]" style="animation-delay: 0.5s;"></div>
            <div class="particle w-3 h-3 bg-orange-200/30 bottom-[30%] right-[10%]" style="animation-delay: 1.5s;"></div>
            {{-- Large gradient blob --}}
            <div class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-gradient-to-br from-orange-200/30 to-amber-100/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-[400px] h-[400px] bg-gradient-to-tr from-orange-100/40 to-yellow-50/30 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col-reverse lg:flex-row items-center gap-12 lg:gap-16 relative z-10">
            {{-- Text Content --}}
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 bg-orange-100/80 text-orange-600 px-4 py-1.5 rounded-full text-xs font-bold tracking-widest uppercase mb-4 border border-orange-200/50" style="animation: fadeInUp 0.6s ease-out both;">
                    Warisan Kuliner Legendaris
                </div>

                {{-- Coming Soon Badge --}}
                <div class="coming-soon-badge inline-flex items-center gap-2.5 bg-gradient-to-r from-red-500 to-orange-500 text-white px-5 py-2 rounded-full text-sm font-extrabold tracking-wide uppercase mb-6 shadow-lg shadow-red-500/20" style="animation: fadeInUp 0.6s ease-out 0.05s both;">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                    </span>
                    🚀 Segera Hadir di Palu!
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-black text-gray-900 leading-[1.1] mb-6" style="animation: fadeInUp 0.7s ease-out 0.1s both;">
                    Cita Rasa Mie Kering
                    <span class="block gradient-text mt-1">Khas Makassar</span>
                </h1>

                <p class="text-base sm:text-lg text-gray-500 mb-8 max-w-xl mx-auto lg:mx-0 leading-relaxed font-light" style="animation: fadeInUp 0.7s ease-out 0.2s both;">
                    Sajian istimewa perpaduan mie kering dan siraman kuah kental gurih yang kaya rasa, diracik dengan tetap mempertahankan tradisi serta keaslian warisan turun-temurun. <strong class="text-orange-600 font-semibold">Segera hadir untuk menyapa Anda di Kota Palu!</strong>
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start" style="animation: fadeInUp 0.7s ease-out 0.3s both;">
                    <a href="#menu" class="group bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-8 py-4 rounded-2xl text-base font-bold transition-all shadow-lg hover:shadow-xl hover:shadow-orange-500/25 transform hover:-translate-y-1 inline-flex items-center justify-center gap-2">
                        Lihat Menu
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                    <a href="#tentang" class="group bg-white hover:bg-orange-50 text-gray-700 px-8 py-4 rounded-2xl text-base font-bold transition-all shadow-md hover:shadow-lg border border-gray-200 hover:border-orange-200 inline-flex items-center justify-center gap-2 transform hover:-translate-y-1">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        Cerita Kami
                    </a>
                </div>

                <div class="mt-10 flex items-center justify-center lg:justify-start gap-3" style="animation: fadeInUp 0.7s ease-out 0.4s both;">
                    <p class="text-sm text-gray-400 font-medium bg-white/70 px-5 py-2.5 rounded-2xl shadow-sm border border-orange-100/50 inline-flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                        </span>
                        Supported by <strong class="text-orange-500 font-bold tracking-wide"><a href="https://www.instagram.com/mieawa/" target="_blank" rel="noopener noreferrer" class="hover:underline">Mie Awa</a></strong>
                    </p>
                </div>
            </div>

            {{-- Hero Image --}}
            <div class="flex-1 w-full max-w-sm lg:max-w-lg relative" style="animation: fadeInRight 0.8s ease-out 0.2s both;">
                {{-- Animated blob background --}}
                <div class="absolute inset-[-15%] bg-gradient-to-br from-orange-200/60 via-amber-100/40 to-orange-300/30 hero-blob -z-10"></div>

                {{-- Main image circle --}}
                <div class="aspect-square bg-gradient-to-br from-orange-100 to-orange-50 rounded-full overflow-hidden border-[6px] border-white shadow-2xl relative flex items-center justify-center group hero-glow hero-image-float">
                    <img src="{{ asset('images/mie-awa.jpg') }}" class="w-full h-full object-cover transition-transform duration-700 ease-in-out group-hover:scale-110" alt="Mie Awa Khas Makassar dari {{ $settings->store_name ?? 'Kedai Mie-Mie' }}" loading="eager" />
                    {{-- Overlay shimmer --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-orange-900/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                </div>

                {{-- Floating badge --}}
                {{-- <div class="absolute -bottom-4 -left-2 lg:-left-6 bg-white rounded-2xl shadow-xl px-5 py-3 flex items-center gap-3 border border-orange-100" style="animation: fadeInUp 0.6s ease-out 0.6s both;">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-500 rounded-xl flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium">Rating</p>
                        <p class="text-sm font-bold text-gray-800">4.9 / 5.0</p>
                    </div>
                </div> --}}

                {{-- Floating badge right --}}
                <div class="absolute -top-2 -right-2 lg:-right-4 bg-white rounded-2xl shadow-xl px-4 py-3 border border-orange-100" style="animation: fadeInUp 0.6s ease-out 0.8s both;">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🔥</span>
                        <div>
                            <p class="text-xs text-gray-400 font-medium">Numero Uno</p>
                            <p class="text-sm font-bold text-orange-600">Mie Kering</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════ Coming Soon / Countdown Section ═══════════════ --}}
    <section id="coming-soon" class="relative py-20 lg:py-28 overflow-hidden">
        {{-- Background --}}
        <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900"></div>
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl"></div>
            {{-- Animated particles --}}
            <div class="particle w-2 h-2 bg-orange-400/20 top-[10%] left-[15%]" style="animation-delay: 0s;"></div>
            <div class="particle w-3 h-3 bg-orange-300/15 top-[30%] right-[20%]" style="animation-delay: 1.5s;"></div>
            <div class="particle w-2 h-2 bg-amber-400/20 bottom-[20%] left-[30%]" style="animation-delay: 0.7s;"></div>
            <div class="particle w-2 h-2 bg-orange-300/15 top-[60%] right-[10%]" style="animation-delay: 2s;"></div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center">
                {{-- Coming soon label --}}
                <div class="animate-on-scroll">
                    <div class="coming-soon-ribbon inline-flex items-center gap-2 bg-gradient-to-r from-orange-500/20 to-amber-500/20 backdrop-blur-sm text-orange-300 px-5 py-2 rounded-full text-xs font-bold tracking-[0.2em] uppercase mb-8 border border-orange-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Grand Opening
                    </div>
                </div>

                <h2 class="animate-on-scroll delay-1 text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-4 leading-tight">
                    Hitung Mundur Menuju
                    <span class="block gradient-text mt-1">Grand Opening!</span>
                </h2>
                <p class="animate-on-scroll delay-2 text-base sm:text-lg text-gray-400 max-w-2xl mx-auto mb-12 font-light leading-relaxed">
                    Kami sedang mempersiapkan yang terbaik untuk menyajikan kelezatan Mie Kering Mie Awa khas Makassar di Kota Palu. Nantikan kehadiran kami!
                </p>

                {{-- Countdown Timer --}}
                <div class="animate-on-scroll delay-3 grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6 max-w-2xl mx-auto mb-14">
                    {{-- Days --}}
                    <div class="countdown-card rounded-2xl p-5 sm:p-6 text-center" style="animation: glow-ring 3s ease-in-out infinite;">
                        <p id="countdown-days" class="countdown-number text-4xl sm:text-5xl lg:text-6xl font-black leading-none mb-1">00</p>
                        <p class="text-gray-500 text-xs sm:text-sm font-semibold uppercase tracking-wider">Hari</p>
                    </div>
                    {{-- Hours --}}
                    <div class="countdown-card rounded-2xl p-5 sm:p-6 text-center" style="animation: glow-ring 3s ease-in-out infinite 0.5s;">
                        <p id="countdown-hours" class="countdown-number text-4xl sm:text-5xl lg:text-6xl font-black leading-none mb-1">00</p>
                        <p class="text-gray-500 text-xs sm:text-sm font-semibold uppercase tracking-wider">Jam</p>
                    </div>
                    {{-- Minutes --}}
                    <div class="countdown-card rounded-2xl p-5 sm:p-6 text-center" style="animation: glow-ring 3s ease-in-out infinite 1s;">
                        <p id="countdown-minutes" class="countdown-number text-4xl sm:text-5xl lg:text-6xl font-black leading-none mb-1">00</p>
                        <p class="text-gray-500 text-xs sm:text-sm font-semibold uppercase tracking-wider">Menit</p>
                    </div>
                    {{-- Seconds --}}
                    <div class="countdown-card rounded-2xl p-5 sm:p-6 text-center" style="animation: glow-ring 3s ease-in-out infinite 1.5s;">
                        <p id="countdown-seconds" class="countdown-number text-4xl sm:text-5xl lg:text-6xl font-black leading-none mb-1">00</p>
                        <p class="text-gray-500 text-xs sm:text-sm font-semibold uppercase tracking-wider">Detik</p>
                    </div>
                </div>

                {{-- CTA --}}
                <div class="animate-on-scroll delay-4 flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="https://www.instagram.com/kedai_miemie/" target="_blank" rel="noopener noreferrer" class="group bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-8 py-4 rounded-2xl text-base font-bold transition-all shadow-lg hover:shadow-xl hover:shadow-orange-500/25 transform hover:-translate-y-1 inline-flex items-center justify-center gap-2.5">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        Follow Kami di Instagram
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                    <p class="text-gray-500 text-sm font-medium">
                        Ikuti update terbaru kami ✨
                    </p>
                </div>
            </div>
        </div>
    </section>



    {{-- ═══════════════ Tentang Kami Section ═══════════════ --}}
    <section id="tentang" class="py-24 lg:py-32 bg-gradient-to-b from-orange-50/30 to-white relative overflow-hidden">
        {{-- Background pattern --}}
        <div class="absolute inset-0 pointer-events-none opacity-[0.02]" style="background-image: url('data:image/svg+xml,<svg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'><g fill=\'none\' fill-rule=\'evenodd\'><g fill=\'%23f97316\' fill-opacity=\'1\'><path d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/></g></g></svg>');"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="animate-on-scroll cta-section">
                <div class="bg-gradient-to-br from-orange-500 via-orange-600 to-amber-600 rounded-3xl overflow-hidden shadow-2xl relative">
                    {{-- Inner decorations --}}
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/3 -translate-x-1/4"></div>
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-white/[0.03] rounded-full"></div>

                    <div class="p-10 sm:p-14 lg:p-20 text-center relative z-10">
                        <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-orange-100 px-4 py-1.5 rounded-full text-xs font-bold tracking-widest uppercase mb-6 border border-white/10">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                            Cerita Di Balik Rasa
                        </div>

                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-8 leading-tight">
                            Melestarikan Sejarah<br class="hidden sm:block"> dalam Tiap Suapan
                        </h2>

                        <p class="text-base sm:text-lg text-orange-50/90 mb-6 leading-relaxed font-light max-w-3xl mx-auto">
                            Perjalanan panjang Mie Awa telah menjadi bagian tak terpisahkan dari denyut nadi kuliner kota Makassar. Dari gerobak sederhana di kawasan Pecinan hingga menjadi ikon legendaris lintas generasi.
                        </p>
                        <p class="text-base sm:text-lg text-orange-50/90 mb-10 leading-relaxed font-light max-w-3xl mx-auto">
                            Terinspirasi dari kelezatan melegenda tersebut, kami kini berupaya menghadirkan sajian mie spesial untuk menyapa para pencinta kuliner di <strong class="text-white font-bold border-b-2 border-orange-300/50 pb-0.5">Kota Palu</strong> melalui Kedai Mie-Mie.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="https://mks0km.id/showDetail/makassar-dalam-mie" target="_blank" rel="noopener noreferrer" class="group bg-white text-orange-600 hover:bg-orange-50 hover:text-orange-700 px-8 py-4 rounded-2xl text-base font-bold transition-all duration-300 shadow-lg hover:shadow-xl inline-flex items-center justify-center gap-2.5 transform hover:-translate-y-1">
                                <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                Baca Kisah Selengkapnya
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════ Footer ═══════════════ --}}
    <footer class="bg-gray-950 pt-16 pb-8 relative overflow-hidden">
        {{-- Background gradient --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[500px] h-[300px] bg-orange-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">
                {{-- Brand --}}
                <div class="text-center md:text-left">
                    <div class="flex items-center gap-2.5 justify-center md:justify-start mb-4">
                        <img src="{{ asset($settings->store_logo ?? '/logo/default.png') }}" class="h-10 w-auto rounded-xl bg-white/10 p-1" alt="{{ $settings->store_name ?? 'Kedai Mie-Mie' }} Logo" />
                        <span class="font-extrabold text-xl text-white">{{ $settings->store_name ?? 'Kedai Mie-Mie' }}</span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed max-w-xs mx-auto md:mx-0">
                        Menghadirkan sajian Mie Awa khas Makassar yang legendaris untuk pencinta kuliner di Kota Palu.
                    </p>
                </div>

                {{-- Links --}}
                <div class="text-center">
                    <h4 class="text-white font-bold text-sm uppercase tracking-widest mb-4">Navigasi</h4>
                    <div class="space-y-2">
                        <a href="#home" class="block text-gray-400 hover:text-orange-400 text-sm transition-colors">Beranda</a>
                        <a href="#tentang" class="block text-gray-400 hover:text-orange-400 text-sm transition-colors">Tentang Kami</a>
                    </div>
                </div>

                {{-- Social --}}
                <div class="text-center md:text-right">
                    <h4 class="text-white font-bold text-sm uppercase tracking-widest mb-4">Ikuti Kami</h4>
                    <div class="flex gap-3 justify-center md:justify-end">
                        <a href="https://www.instagram.com/kedai_miemie/" target="_blank" rel="noopener noreferrer" class="social-icon text-gray-400 hover:text-white" aria-label="Instagram">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="#" class="social-icon text-gray-400 hover:text-white" aria-label="Facebook">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="border-t border-gray-800/50 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm font-medium">
                    &copy; {{ date('Y') }} {{ $settings->store_name ?? 'Kedai Mie-Mie' }}. Hak cipta dilindungi.
                </p>
                <p class="text-gray-600 text-xs italic flex items-center gap-1.5">
                    Supported by <strong class="text-orange-500 font-semibold"><a href="https://www.instagram.com/mieawa/" target="_blank" rel="noopener noreferrer" class="hover:text-orange-400 transition-colors">Mie Awa</a></strong>
                </p>
            </div>
        </div>
    </footer>

    {{-- ═══════════════ Back to Top Button ═══════════════ --}}
    <button id="back-to-top" class="back-to-top fixed bottom-6 right-6 w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-2xl shadow-lg hover:shadow-xl hover:shadow-orange-500/25 flex items-center justify-center transition-all z-50 transform hover:-translate-y-1" aria-label="Kembali ke atas">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
    </button>

    {{-- ═══════════════ JavaScript ═══════════════ --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ── Navbar scroll effect ──
            const navbar = document.getElementById('navbar');
            const backToTop = document.getElementById('back-to-top');

            function handleScroll() {
                const scrollY = window.scrollY;

                if (scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }

                if (scrollY > 400) {
                    backToTop.classList.add('show');
                } else {
                    backToTop.classList.remove('show');
                }
            }

            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll();

            // ── Back to top ──
            backToTop.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            // ── Mobile menu toggle ──
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIconOpen = document.getElementById('menu-icon-open');
            const menuIconClose = document.getElementById('menu-icon-close');
            let menuOpen = false;

            mobileMenuBtn.addEventListener('click', function() {
                menuOpen = !menuOpen;
                mobileMenu.classList.toggle('open', menuOpen);
                menuIconOpen.style.opacity = menuOpen ? '0' : '1';
                menuIconOpen.style.transform = menuOpen ? 'scale(0.75) rotate(90deg)' : 'scale(1) rotate(0)';
                menuIconClose.style.opacity = menuOpen ? '1' : '0';
                menuIconClose.style.transform = menuOpen ? 'scale(1) rotate(0)' : 'scale(0.75) rotate(-90deg)';
            });

            // Close mobile menu on link click
            mobileMenu.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function() {
                    menuOpen = false;
                    mobileMenu.classList.remove('open');
                    menuIconOpen.style.opacity = '1';
                    menuIconOpen.style.transform = 'scale(1) rotate(0)';
                    menuIconClose.style.opacity = '0';
                    menuIconClose.style.transform = 'scale(0.75) rotate(-90deg)';
                });
            });

            // ── Scroll animations (Intersection Observer) ──
            const animatedElements = document.querySelectorAll('.animate-on-scroll');

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            animatedElements.forEach(function(el) {
                observer.observe(el);
            });

            // ── Active nav link highlight ──
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.nav-link');

            function highlightActiveNav() {
                const scrollPos = window.scrollY + 100;

                sections.forEach(function(section) {
                    const top = section.offsetTop;
                    const height = section.offsetHeight;
                    const id = section.getAttribute('id');

                    if (scrollPos >= top && scrollPos < top + height) {
                        navLinks.forEach(function(link) {
                            link.classList.remove('active');
                            if (link.getAttribute('href') === '#' + id) {
                                link.classList.add('active');
                            }
                        });
                    }
                });
            }

            window.addEventListener('scroll', highlightActiveNav, { passive: true });
            highlightActiveNav();

            // ── Countdown Timer ──
            // Ubah tanggal target grand opening di sini (format: YYYY-MM-DD)
            const grandOpeningDate = new Date('2026-03-27T10:00:00+08:00').getTime();

            const daysEl = document.getElementById('countdown-days');
            const hoursEl = document.getElementById('countdown-hours');
            const minutesEl = document.getElementById('countdown-minutes');
            const secondsEl = document.getElementById('countdown-seconds');

            function updateCountdown() {
                const now = new Date().getTime();
                const distance = grandOpeningDate - now;

                if (distance <= 0) {
                    daysEl.textContent = '🎉';
                    hoursEl.textContent = '🎉';
                    minutesEl.textContent = '🎉';
                    secondsEl.textContent = '🎉';

                    daysEl.parentElement.querySelector('p:last-child').textContent = '';
                    hoursEl.parentElement.querySelector('p:last-child').textContent = '';
                    minutesEl.parentElement.querySelector('p:last-child').textContent = 'Sudah';
                    secondsEl.parentElement.querySelector('p:last-child').textContent = 'Buka!';
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                daysEl.textContent = String(days).padStart(2, '0');
                hoursEl.textContent = String(hours).padStart(2, '0');
                minutesEl.textContent = String(minutes).padStart(2, '0');
                secondsEl.textContent = String(seconds).padStart(2, '0');
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        });
    </script>
</body>
</html>
