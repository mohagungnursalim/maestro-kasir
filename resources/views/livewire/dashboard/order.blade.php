<div class="py-12">
    @can('Lihat')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <section class="bg-white py-8 antialiased md:py-16">
            <div class="mx-auto max-w-screen-lg px-4">
                <!-- Pencarian Produk -->
                <div>
                    <form wire:submit.prevent="searchProduct" class="max-w-md mx-auto">
                        <label for="search" class="mb-2 text-sm font-medium text-gray-900 sr-only">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input wire:model="search" type="search" id="search" name="search"
                                class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Cari produk.." />
                            <button wire:loading.remove wire:target='search' type="submit"
                                class="text-white absolute end-2.5 bottom-2.5 bg-blue-600 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">Cari</button>
                            <button disabled wire:loading wire:target='search'
                                class="text-white absolute end-2.5 bottom-2.5 bg-blue-600 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">Mencari..</button>
                        </div>
                    </form>
                </div>

               

                @if ($products)
                <div class="container mx-auto mt-4 px-2 sm:px-4">
                    <div class="overflow-x-auto">
                        <div class="flex gap-4 w-max scroll-smooth snap-x snap-mandatory">
                            @forelse ($products as $product)
                            @php
                                $isInCart = collect($cart)->contains('id', $product->id);
                            @endphp
                            <div class="w-[45vw] sm:w-[22vw] md:w-[18vw] lg:w-[15vw] xl:w-[12vw] min-w-[160px] flex-shrink-0 snap-start">
                                <div
                                    class="bg-white rounded-lg shadow-sm overflow-hidden transition-all duration-300 hover:shadow-lg 
                                    {{ $isInCart ? 'opacity-75' : '' }} {{ $product->stock < 1 ? 'opacity-75' : '' }} flex flex-col">

                                    <!-- Product Image -->
                                    <div class="relative aspect-square overflow-hidden bg-gray-100">
                                        <img
                                            class="absolute inset-0 w-full h-full object-cover object-center transition-all duration-300 hover:scale-105"
                                            src="{{ asset('storage/' . $product->image) }}"
                                            alt="{{ $product->name }}"
                                            loading="lazy"
                                            onerror="this.src='{{ asset('images/placeholder.png') }}'">
                                    </div>                                    

                                    <!-- Product Details -->
                                    <div class="p-2 sm:p-3 flex-1 flex flex-col">
                                        <h3 class="text-xs sm:text-sm font-medium text-gray-800 line-clamp-2 mb-1 min-h-[2.5rem]"
                                            title="{{ $product->name }}">
                                            {{ $product->name }}
                                        </h3>

                                        <div class="mt-auto space-y-2">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm sm:text-base font-bold text-gray-900">
                                                    Rp{{ number_format($product->price, 2, ',', '.') }}
                                                </span>

                                                <span
                                                    class="@if($product->stock < 1) bg-red-100 text-red-800 
                                                            @elseif($product->stock < 10) bg-yellow-100 text-yellow-800 
                                                            @else bg-blue-100 text-blue-800 
                                                            @endif 
                                                            inline-flex items-center text-[10px] sm:text-xs px-1.5 py-0.5 rounded-full">
                                                    {{ $product->stock > 0 ? $product->stock : 'Habis' }}
                                                </span>
                                            </div>

                                            <!-- Add to Cart Button -->
                                            <div class="w-full">
                                                @if (!$isInCart)
                                                <button wire:click="addToCart({{ $product->id }})" wire:loading.remove
                                                    wire:target="addToCart({{ $product->id }})" onclick="playSelectSound()"
                                                    @if($product->stock < 1) disabled @endif
                                                    class="w-full bg-gray-900 text-white py-1.5 sm:py-2 px-2 sm:px-3 rounded text-xs sm:text-sm
                                                        hover:bg-gray-800 transition duration-200 flex items-center 
                                                        justify-center gap-1 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                    <span>Tambah</span>
                                                </button>

                                                <button wire:loading wire:target="addToCart({{ $product->id }})"
                                                    class="w-full bg-gray-900 text-white py-1.5 sm:py-2 px-2 sm:px-3 rounded text-xs sm:text-sm
                                                        flex items-center justify-center gap-1 cursor-wait">
                                                    <svg class="animate-spin w-3 h-3 sm:w-4 sm:h-4" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                            stroke-width="4" />
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                                    </svg>
                                                </button>
                                                @else
                                                <button disabled
                                                    class="w-full bg-green-100 text-green-800 py-1.5 sm:py-2 px-2 sm:px-3 rounded text-xs sm:text-sm
                                                        flex items-center justify-center gap-1 cursor-not-allowed">
                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>Di Keranjang</span>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-span-full">
                                <div class="flex flex-col items-center justify-center py-6 bg-gray-50 rounded-lg">
                                    <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-gray-500 text-sm">Produk tidak ditemukan</p>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @endif




                <!-- Keranjang Belanja -->
                <h2 class="text-xl font-semibold text-gray-900 sm:text-2xl mt-6">Pesanan</h2>
                <div class="mt-6 sm:mt-8 md:gap-6 lg:flex lg:items-start">
                    <div class="w-full lg:w-2/3 max-h-[60vh] overflow-y-auto custom-scrollbar">
                        <div class="space-y-6">
                            @if (empty($cart))
                            <div class="text-center mt-9">
                                <h2 class="text-gray-500">Pesanan Kosong <i class="bi bi-receipt"></i></h2>
                            </div>
                            @else
                            @foreach ($cart as $index => $item)
                            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm md:p-6">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                                    <div class="flex-1">
                                        <span class="text-base font-medium text-gray-900">{{ $item['name'] }}</span>
                                        <div class="flex items-center gap-4 mt-2">
                                            <button wire:loading.remove wire:target='removeFromCart({{ $index }})'
                                                wire:click="removeFromCart({{ $index }})"
                                                onclick="event.stopPropagation(); playSelectSound()" type="button"
                                                class="text-sm text-red-600 hover:underline">Hapus</button>
                                            <button wire:loading wire:target='removeFromCart({{ $index }})'
                                                type="button" class="text-sm text-red-600 hover:underline">
                                                Hapus
                                                <svg class="inline w-4 h-4 text-gray-900 animate-spin ml-2"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8v4a4 4 0 100 8v4a8 8 0 01-8-8z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between md:order-3 md:justify-end">

                                        <div x-data="{ quantity: {{ $item['quantity'] }} }" class="flex items-center">
                                            <!-- Tombol Kurangi (-) dengan delay 500ms -->
                                            <button type="button" x-on:click="
                                                    if (quantity > 1) { 
                                                        quantity--; 
                                                        clearTimeout(window.qtyTimeout);
                                                        window.qtyTimeout = setTimeout(() => { 
                                                            $wire.updateQuantity({{ $index }}, quantity); 
                                                        }, 500);
                                                    }"
                                                class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-gray-100 hover:bg-gray-200 focus:outline-none">
                                                <svg class="h-2.5 w-2.5 text-gray-900" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                                    <path stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2" d="M1 9h16"></path>
                                                </svg>
                                            </button>


                                            <!-- Input Jumlah -->
                                            <input id="cart.{{ $index }}.quantity" name="cart.{{ $index }}.quantity"
                                                type="number" min="1" x-model="quantity"
                                                x-on:input.debounce.500ms="$wire.updateQuantity({{ $index }}, quantity)"
                                                class="w-14 border-0 bg-transparent text-center text-sm font-medium text-gray-900 focus:outline-none">

                                            <!-- Tombol Tambah (+) dengan delay 500ms -->
                                            <button type="button" x-on:click="
                                                            quantity++;
                                                            clearTimeout(window.qtyTimeout);
                                                            window.qtyTimeout = setTimeout(() => { 
                                                                $wire.updateQuantity({{ $index }}, quantity); 
                                                            }, 500);
                                                        "
                                                class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-gray-100 hover:bg-gray-200 focus:outline-none">
                                                <svg class="h-2.5 w-2.5 text-gray-900" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                                    <path stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16">
                                                    </path>
                                                </svg>
                                            </button>

                                        </div>

                                        <div class="text-end md:order-4 md:w-32">
                                            <p class="text-base font-bold text-gray-900">
                                                Rp{{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}
                                            </p>                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                        <!-- Tombol Konfirmasi -->
                        @if (!empty($cart))
                        <div class="flex justify-center mt-6">
                            <button 
                                x-data 
                                @click="$dispatch('open-print-preview')" 
                                class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md block text-white bg-gray-700 hover:bg-gray-800  transition">
                                Cetak Tagihan/Tiket
                            </button>
                        </div>
                        @endif

                        <!-- Modal Konfirmasi -->
                        <div 
                        x-data="{ show: false }" 
                        x-on:open-print-preview.window="show = true" 
                        x-show="show" 
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
                        x-cloak>
                        <div class="bg-white p-6 rounded-md w-96 shadow-lg">
                            <h2 class="text-lg font-bold mb-4 text-gray-800">Konfirmasi</h2>
                            <p class="text-gray-600 mb-4">Ingin cetak tagihan di tab baru?</p>

                            <div class="flex justify-center space-x-2">

                                {{-- Batal Cetak --}}
                                <button 
                                    @click="show = false" 
                                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                                    Batal
                                </button>
                            
                                {{-- Tombol Cetak --}}
                                <button 
                                    wire:click="billPayment"
                                    wire:loading.attr="disabled"
                                    @click="show = false"
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                    Cetak
                                </button>
                            </div>                            
                            
                        </div>
                        </div>

                    </div>


                    <!-- Total dan Pembayaran -->
                    <div class="w-full lg:w-1/3 mt-6 lg:mt-0">
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <p class="text-red-500 text-sm">*Harap terima uangnya sebelum proses pembayaran dimulai!</p>
                            <div class="space-y-4 mt-4">

                                {{-- Pajak --}}
                                <div x-data="{ 
                                            useTax: false, 
                                            taxPercentage: @entangle('tax_percentage'),
                                            resetTax() {
                                                if (!this.useTax) {
                                                    $wire.tax_percentage = 0;
                                                    $wire.call('calculateTotal'); // 🚀 Paksa Livewire update total setelah reset pajak
                                                } 
                                            }
                                        }">

                                    <!-- Toggle Pajak -->
                                    <label
                                        class="inline-flex items-center me-5 peer-disabled:cursor-not-allowed cursor-pointer">
                                        <input type="checkbox" id="tax" name="tax" class="sr-only peer disabled:cursor-not-allowed"
                                            x-model="useTax" x-on:change="resetTax()">
                                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 
                                                peer-focus:ring-purple-300 peer-checked:after:translate-x-full 
                                                rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] 
                                                after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border 
                                                after:rounded-full after:h-5 after:w-5 after:transition-all 
                                                peer-checked:bg-purple-600 peer-disabled:bg-gray-400">
                                        </div>
                                        <span
                                            class="ms-3 text-sm font-medium text-gray-900 peer-disabled:text-gray-400">
                                            Gunakan Pajak
                                        </span>
                                    </label>

                                    <!-- Input Pajak (Persentase) -->
                                    <div class="mt-2 flex space-x-2" x-show="useTax" x-transition>
                                        <input @if (empty($cart)) disabled @endif type="number" id="tax_percentage"
                                            name="tax_percentage" wire:model.live.debounce.300ms="tax_percentage"
                                            x-on:input="() => $wire.call('calculateTotal')" class="w-full p-2 disabled:cursor-not-allowed text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs 
                                           focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Masukkan pajak (contoh: 11 untuk 11%)">
                                        <span
                                            class="p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs">%</span>
                                    </div>

                                    <!-- Pajak dalam Rupiah (Hanya Ditampilkan) -->
                                    <dl class="flex justify-between mt-3" x-show="useTax" x-transition>
                                        <dt class="text-gray-500">PB1 (<span
                                                x-text="useTax ? taxPercentage : 0"></span>%)</dt>
                                        @if (!empty($cart)) <span
                                            class="font-bold p-2 text-red-500 border border-gray-300 rounded-lg bg-gray-50 text-xs">Rp{{ number_format($tax, 2, ',', '.') }}</span>
                                        @endif
                                    </dl>

                                </div>

                                {{-- Diskon --}}
                                <div x-data="{
                                        useDiscount: false,
                                        init() {
                                            this.$watch('useDiscount', value => {
                                                if (!value) {
                                                    $wire.set('discount_percentage', 0)
                                                    $wire.call('calculateTotal')
                                                }
                                            })
                                        }
                                    }">
                                    <!-- Toggle Diskon -->
                                    <label class="inline-flex items-center me-5 peer-disabled:cursor-not-allowed cursor-pointer">
                                        <input type="checkbox" id="discount" name="discount"
                                            class="sr-only peer disabled:cursor-not-allowed"
                                            x-model="useDiscount">
                                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 
                                            peer-focus:ring-purple-300 peer-checked:after:translate-x-full 
                                            rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] 
                                            after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border 
                                            after:rounded-full after:h-5 after:w-5 after:transition-all 
                                            peer-checked:bg-purple-600 peer-disabled:bg-gray-400">
                                        </div>
                                        <span class="ms-3 text-sm font-medium text-gray-900 peer-disabled:text-gray-400">
                                            Gunakan Diskon
                                        </span>
                                    </label>
                                
                                    <!-- Input Diskon -->
                                    <div class="mt-2 flex space-x-2" x-show="useDiscount" x-transition>
                                        <input @if (empty($cart)) disabled @endif type="number"
                                            id="discount_percentage" name="discount_percentage"
                                            wire:model.live.debounce.300ms="discount_percentage"
                                            class="w-full p-2 disabled:cursor-not-allowed text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs 
                                            focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Masukkan diskon (contoh: 20 untuk 20%)">
                                        <span class="p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs">%</span>
                                    </div>
                                
                                    <!-- Tampilkan Nilai Diskon dalam Rupiah -->
                                    <dl class="flex justify-between mt-3" x-show="useDiscount" x-transition>
                                        <dt class="text-gray-500">Diskon ({{ $discount_percentage }}%)</dt>
                                        @if (!empty($cart))
                                            <span class="font-bold p-2 text-red-500 border border-gray-300 rounded-lg bg-gray-50 text-xs">
                                                Rp{{ number_format($discount, 2, ',', '.') }}
                                            </span>
                                        @endif
                                    </dl>
                                </div>
                                

                                {{-- Metode Pembayaran --}}
                                <div 
                                    x-data="{ 
                                        paymentMethod: @entangle('payment_method'), 
                                        isCash() { return this.paymentMethod === 'cash'; }
                                    }"
                                    x-init="$watch('paymentMethod', value => {
                                        if (value !== 'cash') {
                                            @this.set('customerMoney', 0);
                                        }
                                    })">

                                    <!-- Pilih Metode Pembayaran -->
                                    <div>
                                        <select x-model="paymentMethod" wire:model="payment_method" id="paymentMethod" name="paymentMethod"
                                            :disabled="@json(empty($cart))" class="block py-2.5 px-0 w-full text-sm text-gray-500 bg-transparent border-0 border-b-2 
                                                   border-gray-200 appearance-none focus:outline-none focus:ring-0 
                                                   focus:border-gray-200 peer disabled:cursor-not-allowed">

                                            <option value="cash">Tunai</option>
                                            <option value="credit_card">Kartu Kredit</option>
                                            <option value="debit_card">Kartu Debit</option>

                                            <optgroup label="E-Wallet">
                                                <option value="ewallet">E-Wallet (Umum)</option>
                                                <option value="gopay">GoPay</option>
                                                <option value="ovo">OVO</option>
                                                <option value="dana">DANA</option>
                                                <option value="linkaja">LinkAja</option>
                                                <option value="shopeepay">ShopeePay</option>
                                                <option value="grabpay">GrabPay</option>
                                            </optgroup>

                                            <optgroup label="Transfer Bank">
                                                <option value="bca">BCA</option>
                                                <option value="bri">BRI</option>
                                                <option value="mandiri">Mandiri</option>
                                                <option value="bni">BNI</option>
                                            </optgroup>

                                        </select>
                                    </div>

                                    <!-- Input Uang Pelanggan -->
                                    <div class="mt-3">
                                        <label for="customerMoney"
                                            class="block mb-2 text-sm font-medium text-gray-900">Uang Pelanggan</label>
                                        <input type="number" id="customerMoney" name="customerMoney"
                                            wire:model.live.debounce.800ms="customerMoney"
                                            :disabled="@json(empty($cart)) || !isCash()"
                                            class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs 
                                                   focus:ring-blue-500 focus:border-blue-500 disabled:cursor-not-allowed" placeholder="Masukkan uang pelanggan...">
                                    </div>

                                    <!-- Catatan -->
                                    <template x-if="!isCash()">
                                        <p class="text-xs text-gray-500 mt-1 italic">
                                            Tidak perlu input uang pelanggan untuk metode non-tunai.
                                        </p>
                                    </template>
                                </div>

                                {{-- Subtotal --}}
                                <dl class="flex justify-between">
                                    <dt class="text-gray-500">Sub Total</dt>
                                    @if (!empty($cart)) <span
                                        class="font-bold p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs">Rp{{ number_format($subtotal, 2, ',', '.') }}</span>
                                    @endif
                                </dl>

                                {{-- Total Bayar --}}
                                <dl class="flex justify-between border-t pt-2">
                                    <dt class="font-bold text-gray-900">Total Bayar</dt>
                                    @if (!empty($cart)) <span
                                        class="font-bold p-2 text-gray-900 border border-blue-300 rounded-lg bg-blue-50 text-xs">Rp{{ number_format($total, 2, ',', '.') }}</span>
                                    @endif
                                </dl>

                                {{-- Uang Kembali --}}
                                <dl class="flex justify-between">
                                    <dt class="font-bold text-gray-900">Uang Kembali</dt>
                                    @if (!empty($cart)) <span
                                        class="font-bold p-2 text-gray-900 border border-green-300 rounded-lg bg-green-50 text-xs">Rp{{ number_format($change, 2, ',', '.') }}</span>
                                    @endif
                                </dl>
                            </div>
                            <div class="flex justify-center mt-4">
                                <!-- Tombol Proses -->
                                @if (!empty($cart))
                                <button x-data="{ disabled: false }" :disabled="disabled"
                                    x-on:click="disabled = true; setTimeout(() => disabled = false, 2000)"
                                    wire:click="processOrder" wire:loading.remove wire:target="processOrder"
                                    wire:loading.attr="disabled" type="button"
                                    class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                    @disabled(empty($cart))>
                                    Proses
                                </button>

                                @endif

                                <!-- Tombol Loading saat Memproses -->
                                <button disabled wire:loading wire:target="processOrder"
                                    class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Memproses..
                                    <svg class="inline w-4 h-4 text-white animate-spin ml-2"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 100 8v4a8 8 0 01-8-8z"></path>
                                    </svg>
                                </button>

                                @if (!empty($cart))
                                <!-- Tombol Kosongkan Pesanan -->
                                <button wire:loading.remove wire:click="resetCart" wire:loading.attr="disabled"
                                    onclick="event.stopPropagation(); playSelectSound()" type="button"
                                    class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Kosongkan Pesanan
                                </button>

                                <!-- Tombol Loading saat Reset -->
                                <button disabled wire:loading wire:target="resetCart"
                                    class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Loading..
                                    <svg class="inline w-4 h-4 text-white animate-spin ml-2"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
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
    @endcan
    <script>
        Livewire.on('printReceipt', orderId => {
            let receiptUrl = `/dashboard/order-receipt/${orderId}`;
            let printWindow = window.open(receiptUrl, '_blank', 'width=640,height=600');

            if (printWindow) {
                printWindow.focus();
            } else {
                alert("Popup blocked! Please allow popups for this site.");
            }
        });
    </script>

    <script>
        window.addEventListener('showBillPrintPopup', event => {
            const url = event.detail;
            window.open(url, '_blank', 'width=640,height=600');
        });
    </script>

</div>
</div>
