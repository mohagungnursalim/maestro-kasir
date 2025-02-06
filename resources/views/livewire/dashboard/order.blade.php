<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <section class="bg-white py-8 antialiased dark:bg-gray-900 md:py-16">
            <div class="mx-auto max-w-screen-lg px-4">
                <!-- Pencarian Produk -->
                <div>
                    <form wire:submit.prevent="searchProduct" class="max-w-md mx-auto">
                        <label for="search" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                            <input wire:model="search" type="search" id="search" name="search" class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Cari produk.." />
                            <button wire:loading.remove wire:target='search' type="submit" class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">Cari</button>
                            <button disabled wire:loading wire:target='search' class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">Mencari..</button>
                        </div>
                    </form>
                </div>

                <!-- Daftar Produk Hasil Pencarian -->
                @if ($products)
                <div class="mt-4">
                    @foreach ($products as $product)
                        <div class="flex items-center justify-between p-2 border-b">
                            <img width="50px" src="{{ asset('storage/' . $product->image) }}" alt="">
                            <span class="flex items-center">
                                <a class="text-bold">{{ $product->name }}</a> - Rp{{ number_format($product->price, 0, ',', '.') }} | Stok: 
                                <span class="bg-blue-100 text-dark text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">
                                    {{ $product->stock }}
                                </span>
                            </span>
                            
                            @php
                                $isInCart = collect($cart)->contains('id', $product->id);
                            @endphp
                            <button {{ $isInCart ? 'disabled' : '' }} wire:loading.remove wire:target='addToCart({{ $product->id }})' wire:click="addToCart({{ $product->id }})" onclick="playSelectSound()"  class="px-2 py-1 {{ $isInCart ? 'bg-gray-200' : 'bg-gray-900' }} text-white rounded">Pilih</button>
                            <button {{ $isInCart ? 'disabled' : '' }} wire:loading wire:target='addToCart({{ $product->id }})' class="px-2 py-1 bg-gray-900 opacity-50 text-white rounded">
                                Pilih
                                <svg class="inline w-4 h-4 text-white animate-spin ml-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 100 8v4a8 8 0 01-8-8z"></path>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
                @endif

                <!-- Keranjang Belanja -->
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl mt-6">Pesanan</h2>
                <div class="mt-6 sm:mt-8 md:gap-6 lg:flex lg:items-start">
                    <div class="w-full lg:w-2/3">
                        <div class="space-y-6">
                            @foreach ($cart as $index => $item)
                                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 md:p-6">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                                        <div class="flex-1">
                                            <span class="text-base font-medium text-gray-900 dark:text-white">{{ $item['name'] }}</span>
                                            <div class="flex items-center gap-4 mt-2">
                                                <button wire:loading.remove wire:target='removeFromCart({{ $index }})' wire:click="removeFromCart({{ $index }})" onclick="playSelectSound()" type="button" class="text-sm text-red-600 hover:underline dark:text-red-500">Hapus</button>
                                                <button wire:loading wire:target='removeFromCart({{ $index }})' type="button" class="text-sm text-red-600 hover:underline dark:text-red-500">
                                                    Hapus
                                                    <svg class="inline w-4 h-4 text-gray-900 animate-spin ml-2" xmlns="http://www.w3.org/2000/svg"
                                                        fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                                        </circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8v4a4 4 0 100 8v4a8 8 0 01-8-8z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between md:order-3 md:justify-end">
                                            <div class="flex items-center">
                                                @if ($item['quantity'] > 1)
                                                    <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})" onclick="playSelectSound()" type="button" class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                                                        <svg class="h-2.5 w-2.5 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 9h16"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                                <input id="cart.{{ $index }}.quantity" name="cart.{{ $index }}.quantity"
                                                    type="number" min="1"
                                                    wire:model.live.debounce.500ms="cart.{{ $index }}.quantity"
                                                    class="w-14 border-0 bg-transparent text-center text-sm font-medium text-gray-900 focus:outline-none focus:ring-0 dark:text-white">


                                                <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})" onclick="playSelectSound()" type="button" class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                                                    <svg class="h-2.5 w-2.5 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="text-end md:order-4 md:w-32">
                                                <p class="text-base font-bold text-gray-900 dark:text-white">Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                   <!-- Total dan Pembayaran -->
                    <div class="w-full lg:w-1/3 mt-6 lg:mt-0">
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-red-500 text-sm">*Harap terima dahulu uangnya baru proses pembayaran!</p>
                            <div class="space-y-4 mt-4">
                                <div>
                                    <label for="customerMoney" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Uang Pelanggan</label>
                                    <input
                                        type="number" 
                                        id="customerMoney" 
                                        name="customerMoney"
                                        wire:model="customerMoney"
                                        class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        placeholder="Masukkan uang pelanggan..."
                                    >
                                </div>
                                <dl class="flex justify-between">
                                    <dt class="text-gray-500">Sub Total</dt>
                                    <dd class="text-gray-900">Rp{{ number_format($subtotal, 0, ',', '.') }}</dd>
                                </dl>
                                <dl class="flex justify-between">
                                    <dt class="text-gray-500">PPN (11%)</dt>
                                    <dd class="text-red-500">Rp{{ number_format($tax, 0, ',', '.') }}</dd>
                                </dl>
                                <dl class="flex justify-between border-t pt-2">
                                    <dt class="font-bold text-gray-900">Total</dt>
                                    <dd class="font-bold text-gray-900">Rp{{ number_format($total, 0, ',', '.') }}</dd>
                                </dl>
                                <div>
                                    <dt class="text-gray-500">Kembalian</dt>
                                    <button disabled class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-cyan-500 to-blue-500 hover:text-gray-900 focus:ring-4 focus:outline-none focus:ring-cyan-200">
                                        <span class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md">
                                            Rp{{ number_format($change, 0, ',', '.') }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <div class="flex justify-center mt-4">
                                <button wire:loading.remove
                                    wire:click="processOrder" 
                                    type="button" 
                                    class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                    {{ empty($cart) ? 'disabled' : '' }}>
                                    Proses
                                </button>
                                <button disabled wire:loading wire:target='processOrder' 
                                    class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Memproses..
                                    <svg class="inline w-4 h-4 text-white animate-spin ml-2" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 100 8v4a8 8 0 01-8-8z"></path>
                                    </svg>
                                </button>
                            

                                @if (!empty($cart))
                                    <button wire:loading.remove
                                        wire:click="resetCart" onclick="playSelectSound()"
                                        type="button" 
                                        class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">
                                        Bersihkan Pesanan
                                    </button>
                                    <button disabled wire:loading wire:target='resetCart'
                                        class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Loading..
                                        <svg class="inline w-4 h-4 text-white animate-spin ml-2" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                            </circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8v4a4 4 0 100 8v4a8 8 0 01-8-8z"></path>
                                        </svg>
                                    </button>
                                @endif

                            </div>
                        </div>
                    </div>
              
                    
                </div>
            </div>
        </section>
    </div>
</div>






{{-- Sweet alert,payment success --}}
<script>
    Livewire.on('successPayment', (event) => {
         // Buat elemen audio
         let audio = new Audio("{{ asset('audio/success-sound.mp3') }}"); 

        // Putar audio
        audio.play().catch(error => console.error("Gagal memutar audio:", error));  

        const Toast = Swal.mixin({
                 toast: true,
                 position: "top-end",
                 showConfirmButton: false,
                 timer: 10000,
                 timerProgressBar: true,
                 didOpen: (toast) => {
                     toast.onmouseenter = Swal.stopTimer;
                     toast.onmouseleave = Swal.resumeTimer;
                 }
             });
             Toast.fire({
                 icon: "success",
                 title: "Pembayaran berhasil 🙌!"
             });
 
     });
 
 </script>

 {{-- Sweet alert,payment null --}}
<script>
    Livewire.on('nullPaymentSelected', (event) => {
        
        // Buat elemen audio
        let audio = new Audio("{{ asset('audio/error-sound.mp3') }}"); 

        // Putar audio
        audio.play().catch(error => console.error("Gagal memutar audio:", error)); 

        const Toast = Swal.mixin({
                 toast: true,
                 position: "top-end",
                 showConfirmButton: false,
                 timer: 10000,
                 timerProgressBar: true,
                 didOpen: (toast) => {
                     toast.onmouseenter = Swal.stopTimer;
                     toast.onmouseleave = Swal.resumeTimer;
                 }
             });
             Toast.fire({
                 icon: "error",
                 title: "Produk harus dipilih 🤔 sebelum melanjutkan pembayaran!"
             });
 
     });
 
</script>

 {{-- Sweet alert,payment error --}}
<script>
    Livewire.on('errorPayment', (event) => {
             const Toast = Swal.mixin({
                 toast: true,
                 position: "top-end",
                 showConfirmButton: false,
                 timer: 10000,
                 timerProgressBar: true,
                 didOpen: (toast) => {
                     toast.onmouseenter = Swal.stopTimer;
                     toast.onmouseleave = Swal.resumeTimer;
                 }
             });
             Toast.fire({
                 icon: "error",
                 title: "Pembayaran gagal 🥲!"
             });
 
     });
 </script>
 
 {{-- Sweet alert,uang kurang --}}
 <script>
    Livewire.on('insufficientPayment', (shortage) => {
        // Buat elemen audio
        let audio = new Audio("{{ asset('audio/error-sound.mp3') }}"); 

        // Putar audio
        audio.play().catch(error => console.error("Gagal memutar audio:", error)); 

        Swal.fire({
            title: "Pembayaran Kurang!",
            text: `Uang pelanggan kurang Rp${shortage.toLocaleString('id-ID')}.Tetap lanjutkan?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Oke",
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('forceProcessOrder'); // Memanggil method Livewire
            }
        });
    });
</script>


{{-- Sweet Alert untuk stok produk kurang --}}
<script>
    Livewire.on('insufficientStock', (insufficientProducts) => {
        if (!Array.isArray(insufficientProducts) || insufficientProducts.length === 0) return;

        let audio = new Audio("{{ asset('audio/error-sound.mp3') }}");
        audio.play().catch(error => console.error("Gagal memutar audio:", error));

        Swal.fire({
            title: "Stok Tidak Cukup!",
            text: "Stok tidak cukup untuk produk berikut:\n\n" + insufficientProducts.join("\n"),
            icon: "warning",
            confirmButtonText: "OK"
        });
    });
</script>
    
</div>

