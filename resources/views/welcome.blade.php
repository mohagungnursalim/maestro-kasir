<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kedai Mie-Mie - Mie Pilihan Terbaik</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-orange-50 text-gray-800 antialiased selection:bg-orange-500 selection:text-white">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm fixed w-full z-10 top-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center gap-2">
                    <img src="{{ asset($settings->store_logo ?? '/logo/default.png') }}" class="h-10 w-auto rounded-full" alt="Logo" />
                    <span class="font-bold text-xl text-orange-600 tracking-tight">{{ $settings->store_name ?? 'Kedai Mie-Mie' }}</span>
                </div>
                <div class="hidden sm:flex space-x-8">
                    <a href="#home" class="text-gray-600 hover:text-orange-500 px-3 py-2 text-sm font-medium transition-colors">Beranda</a>
                    <a href="#menu" class="text-gray-600 hover:text-orange-500 px-3 py-2 text-sm font-medium transition-colors">Menu Pilihan</a>
                    <a href="#tentang" class="text-gray-600 hover:text-orange-500 px-3 py-2 text-sm font-medium transition-colors">Tentang Kami</a>
                </div>
                <div>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-full text-sm font-semibold transition-colors shadow-sm">Dashboard POS</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="pt-28 pb-20 lg:pt-36 lg:pb-28 bg-orange-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col-reverse lg:flex-row items-center gap-12">
            <div class="flex-1 text-center lg:text-left">
                <span class="text-orange-600 font-bold tracking-widest uppercase text-xs mb-3 block">Warisan Kuliner Legendaris</span>
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold text-gray-900 leading-tight mb-6">
                    Cita Rasa Mie Awa <span class="text-orange-500">Khas Makassar</span>
                </h1>
                <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                 Sajian istimewa perpaduan mie garing dan siraman kuah kental gurih yang kaya rasa, diracik dengan tetap mempertahankan tradisi serta keaslian warisan turun-temurun dari sang pelopor.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="#tentang" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3.5 rounded-full text-base font-semibold transition-colors shadow-md hover:shadow-lg transform hover:-translate-y-0.5 inline-block text-center">
                        Tentang Kami
                    </a>
                </div>
                <div class="mt-10 flex items-center justify-center lg:justify-start gap-3">
                    <p class="text-sm text-gray-500 font-medium bg-white/60 px-4 py-2 rounded-full shadow-sm border border-orange-100">
                        Supported by <strong class="text-orange-500 font-bold tracking-wide"><a href="https://www.instagram.com/mieawa/">Mie Awa</a></strong>
                    </p>
                </div>
            </div>
            <div class="flex-1 w-full max-w-md lg:max-w-full relative">
                <!-- Background decorative element -->
                <div class="absolute inset-0 bg-orange-200 rounded-full scale-105 transform translate-x-4 translate-y-4 -z-10 opacity-50"></div>
                <div class="aspect-square bg-orange-100 rounded-full overflow-hidden border-8 border-white shadow-xl relative flex items-center justify-center group">
                    <img src="{{ asset('images/mie-awa.jpg') }}" class="w-full h-full object-cover transition-transform duration-700 ease-in-out group-hover:scale-110" alt="Mie Pilihan Nusantara">
                </div>
            </div>
        </div>
    </section>

    <!-- Features / Menu Highlight Section -->
    <section id="menu" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl mb-4">Menu Pilihan</h2>
                <div class="w-16 h-1.5 bg-orange-500 mx-auto rounded-full mb-6"></div>
                <p class="text-lg text-gray-500 max-w-2xl mx-auto">Menu andalan yang paling banyak dipesan dan menjadi favorit.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-orange-50 rounded-3xl p-8 hover:bg-orange-100 transition-colors duration-300 border border-orange-100">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-orange-500 mb-6 shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Mie Spesial Komplit</h3>
                    <p class="text-gray-600 leading-relaxed">Mie kenyal dengan paduan ayam cincang, pangsit rebus, bakso, dan sayuran segar, disiram kuah kaldu rempah pilihan yang kaya rasa.</p>
                </div>
                
                <!-- Card 2 -->
                <div class="bg-orange-50 rounded-3xl p-8 hover:bg-orange-100 transition-colors duration-300 border border-orange-100">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-orange-500 mb-6 shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Mie Yamin Manis Gurih</h3>
                    <p class="text-gray-600 leading-relaxed">Perpaduan mie lembut dengan kecap manis gurih rahasia, dilengkapi taburan ayam suwir dan pangsit goreng renyah.</p>
                </div>

                <!-- Card 3 -->
                <div class="bg-orange-50 rounded-3xl p-8 hover:bg-orange-100 transition-colors duration-300 border border-orange-100">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-orange-500 mb-6 shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Mie Mercon Level Tingkat</h3>
                    <p class="text-gray-600 leading-relaxed">Bagi pecinta pedas sejati, varian mie racikan cabai asli ini akan memberikan sensasi pedas yang membakar namun nagih tanpa henti.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tentang Kami & Panggilan Menarik -->
    <section id="tentang" class="py-24 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-orange-500 rounded-3xl overflow-hidden shadow-2xl p-10 sm:p-14 lg:p-16 text-center relative z-10">
                <span class="text-orange-200 font-extrabold tracking-widest uppercase text-xs mb-3 block">Cerita Di Balik Rasa</span>
                <h2 class="text-3xl font-extrabold text-white sm:text-4xl mb-6 leading-tight">Melestarikan Sejarah dalam Tiap Suapan</h2>
                <p class="text-lg text-orange-50 mb-10 leading-relaxed font-medium max-w-4xl mx-auto">
                    Perjalanan panjang Mie Awa telah menjadi bagian tak terpisahkan dari denyut nadi kuliner kota Makassar. Dari gerobak sederhana di kawasan Pecinan hingga menjadi ikon legendaris lintas generasi. 
                    <br><br>
                    Terinspirasi dari kelezatan melegenda tersebut, kami kini berupaya menghadirkan sajian mie spesial untuk menyapa para pencinta kuliner di <strong class="text-white font-bold border-b border-orange-300 pb-0.5">Kota Palu</strong> melalui Kedai Mie-Mie.
                </p>
                <div class="flex justify-center">
                    <a href="https://mks0km.id/showDetail/makassar-dalam-mie" target="_blank" rel="noopener noreferrer" class="bg-white text-orange-600 hover:bg-orange-50 hover:text-orange-700 px-8 py-3.5 rounded-full text-base font-bold transition-all duration-300 shadow-lg hover:shadow-xl inline-flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        Baca Kisah Selengkapnya
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <img src="{{ asset($settings->store_logo ?? '/logo/default.png') }}" class="h-8 w-auto rounded-md bg-white p-1" alt="Logo" />
                <span class="font-bold text-xl text-white">{{ $settings->store_name ?? 'Kedai Mie-Mie' }}</span>
            </div>
            <div class="flex flex-col items-center md:items-start text-center md:text-left gap-1">
                <p class="text-gray-500 text-sm font-medium">
                    &copy; {{ date('Y') }} Kedai Mie-Mie.
                </p>
                <p class="text-gray-600 text-xs italic">
                    Supported by <strong class="text-orange-500 font-semibold">Mie Awa</strong>
                </p>
            </div>
            <div class="flex space-x-6">
                <a href="#" class="text-gray-500 hover:text-orange-500 transition-colors">
                    Instagram
                </a>
                <a href="#" class="text-gray-500 hover:text-orange-500 transition-colors">
                    Facebook
                </a>
            </div>
        </div>
    </footer>

</body>
</html>
