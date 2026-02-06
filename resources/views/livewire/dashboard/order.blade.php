<div class="py-12">
    @can('Lihat')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <section class="bg-yellow-50 py-8 antialiased md:py-16">
            <div class="mx-auto max-w-screen-lg px-4">
                <!-- Pencarian Produk -->
                <div>
                    <form wire:submit.prevent="searchProduct" class="max-w-md mx-auto">
                        <label for="search" class="mb-2 text-sm font-medium text-gray-900 sr-only">Search</label>

                        <div class="relative">
                            <!-- Icon -->
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>

                            <!-- Input -->
                            <input wire:model.defer="search" type="search" id="search" name="search"
                                class="bg-white block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Cari produk.." />

                            <!-- Button Normal -->
                            <button type="submit" wire:loading.remove wire:target="searchProduct"
                                class="text-gray-900 absolute end-2.5 bottom-2.5 bg-yellow-300 hover:bg-yellow-400 focus:ring-4 focus:outline-none focus:ring-yellow-200 font-medium rounded-lg text-sm px-4 py-2 flex items-center gap-2">
                                Cari
                            </button>

                            <!-- Button Loading -->
                            <button type="button" disabled wire:loading wire:target="searchProduct"
                                class="text-gray-900 absolute end-2.5 bottom-2.5 bg-yellow-300 opacity-70 font-medium rounded-lg text-sm px-4 py-2 flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i>
                            </button>

                        </div>
                    </form>
                </div>

                {{-- Product List --}}
                @if ($products)
                <div class="container mx-auto mt-4 px-4 sm:px-8">
                    <div class="overflow-x-auto">
                        <div class="flex gap-4 w-max mx-auto scroll-smooth snap-x snap-mandatory">
                            @forelse ($products as $product)
                            @php
                            $isInCart = collect($cart)->contains('id', $product->id);
                            @endphp
                            <div
                                class="w-[45vw] sm:w-[22vw] md:w-[18vw] lg:w-[15vw] xl:w-[12vw] min-w-[160px] flex-shrink-0 snap-start">
                                <div
                                    class="bg-white rounded-lg shadow-sm overflow-hidden transition-all duration-300 hover:shadow-lg 
                                    {{ $isInCart ? 'opacity-75' : '' }} {{ $product->stock < 1 ? 'opacity-75' : '' }} flex flex-col">

                                    <div class="flex gap-2 p-2 border rounded-lg hover:shadow-sm transition bg-white">

                                        <!-- Product Image -->
                                        <div class="relative w-14 h-14 overflow-hidden bg-gray-100 rounded">
                                            <img class="w-full h-full object-cover object-center transition-all duration-300 hover:scale-105"
                                                src="{{ asset('storage/' . $product->image) }}"
                                                alt="{{ $product->name }}" loading="lazy"
                                                onerror="this.src='{{ asset('images/placeholder.png') }}'">
                                        </div>

                                        <!-- Product Details -->
                                        <div class="flex-1 flex flex-col min-w-0">

                                            <!-- Product Name -->
                                            <h3 class="text-xs leading-tight font-medium text-gray-800 line-clamp-2 min-h-[2rem]"
                                                title="{{ $product->name }}">
                                                {{ $product->name }}
                                            </h3>

                                            <div class="mt-auto space-y-1">

                                                <!-- Price & Stock -->
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs font-bold text-gray-900">
                                                        Rp{{ number_format($product->price, 0, ',', '.') }}
                                                    </span>

                                                    <span class="@if($product->stock < 1) bg-red-100 text-red-800 
                                                    @elseif($product->stock < 10) bg-yellow-100 text-yellow-800 
                                                    @else bg-blue-100 text-blue-800 
                                                    @endif 
                                                    inline-flex items-center text-xs px-1 py-0.5 rounded">
                                                        {{ $product->stock > 0 ? $product->stock : 'Habis' }}
                                                    </span>
                                                </div>

                                                <!-- Add to Cart Button -->
                                                <div class="w-full">
                                                    @if (!$isInCart)
                                                    <button type="button"
                                                        wire:click="openAddToCartModal({{ $product->id }})"
                                                        wire:target="openAddToCartModal({{ $product->id }})"
                                                        wire:loading.attr="disabled" onclick="playSelectSound()"
                                                        @if($product->stock < 1) disabled @endif class="relative w-full bg-gray-900 text-white py-2 px-1 rounded text-xs
                                                                hover:bg-gray-800 transition
                                                                disabled:bg-gray-300 disabled:cursor-not-allowed
                                                                h-1">

                                                            {{-- Normal --}}
                                                            <span
                                                                class="absolute inset-0 flex items-center justify-center gap-2"
                                                                wire:loading.remove
                                                                wire:target="openAddToCartModal({{ $product->id }})">

                                                                <i class="fas fa-plus"></i>
                                                                <span>Tambah</span>
                                                            </span>

                                                            {{-- Loading --}}
                                                            <span
                                                                class="absolute inset-0 flex items-center justify-center mt-0.5 leading-none"
                                                                wire:loading
                                                                wire:target="openAddToCartModal({{ $product->id }})">

                                                                <i class="fas fa-spinner fa-spin leading-none"></i>
                                                            </span>
                                                    </button>
                                                    @else
                                                    <button disabled class="relative w-full bg-green-600 text-white py-2 px-1 rounded text-xs
                                                                opacity-50 transitiondisabled:cursor-not-allowed
                                                                h-1">

                                                        <span
                                                            class="absolute inset-0 flex items-center justify-center gap-2">
                                                            <span>Ditambahkan</span>
                                                        </span>
                                                    </button>
                                                    @endif
                                                </div>


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


                {{-- Modal Add Catatan per item --}}
                @if($showNoteModal)
                <div x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
                    <!-- Backdrop -->
                    <div
                        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                        @click="$wire.set('showNoteModal', false)"
                    ></div>

                    <!-- Modal Box -->
                    <div x-transition class="relative bg-white w-full max-w-md rounded-xl shadow-2xl p-6">
                        <!-- Icon Note -->
                        <div class="flex justify-center mb-4">
                            <div class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-100 text-gray-600">
                                <i class="fas fa-sticky-note fa-lg"></i>
                            </div>
                        </div>

                        <!-- Title -->
                        <h2 class="text-lg font-bold text-center text-gray-800 mb-2">
                            Tambah Catatan Item
                        </h2>

                        <!-- Desc -->
                        <p class="text-center text-gray-500 text-sm mb-4">
                            Isi jika ada permintaan khusus (opsional).
                        </p>

                        <!-- Textarea -->
                        <textarea
                            wire:model.defer="tempProductNote"
                            rows="3"
                            class="w-full border rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: tanpa gula, extra pedas, dll..."
                        ></textarea>

                        <!-- Actions -->
                        <div class="flex justify-center gap-3 mt-6">
                            <button
                                wire:click="$set('showNoteModal', false)"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
                            >
                                Batal
                            </button>

                            <button
                                wire:click="confirmAddToCart"
                                onclick="playSelectSound()"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                            >
                                Tambahkan
                            </button>
                        </div>
                    </div>
                </div>
                @endif



                <!-- Keranjang Belanja -->
                <h2 class="text-xl font-semibold text-gray-900 sm:text-2xl mt-6">Pesanan</h2>
                <div class="mt-6 sm:mt-8 md:gap-6 lg:flex lg:items-start">

                   <!-- ================== KIRI: DAFTAR ITEM + ACTION ================== -->
                    <div class="w-full lg:w-2/3 flex flex-col h-[70vh]">

                        <!-- ================== SCROLL AREA (ITEM SAJA) ================== -->
                        <div class="flex-1 overflow-y-auto custom-scrollbar pr-1">
                            <div class="space-y-2">

                                @if (empty($cart))
                                    <div class="text-center mt-10 text-sm text-gray-500">
                                        Pesanan Kosong <i class="far fa-list-alt"></i>
                                    </div>
                                @else

                                    @foreach ($cart as $index => $item)

                                    <div wire:key="cart-item-{{ $item['id'] }}-{{ $index }}"class="bg-white border border-gray-100 rounded-lg px-4 py-3 text-sm shadow-sm hover:shadow-md transition-all duration-150">
                                        <div class="flex items-start gap-3">
                                            <!-- INFO ITEM -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start gap-2">

                                                    <div class="min-w-0 cursor-pointer"
                                                        @click="$dispatch('open-edit-note', {
                                                            index: {{ $index }},
                                                            note: @js($item['product_note'] ?? '')
                                                        })">

                                                                <div class="font-semibold text-sm text-gray-900 truncate">
                                                            {{ $item['name'] }}
                                                        </div>

                                                                <div class="text-[11px] text-gray-500 leading-tight line-clamp-1 hover:text-gray-700">{{ $item['product_note'] ?: 'Tambah catatan…' }}</div>
                                                    </div>

                                                    <!-- DELETE -->
                                                    <button
                                                        wire:click="removeFromCart({{ $index }})"
                                                        onclick="event.stopPropagation(); playSelectSound()">
                                                        <i class="fas fa-trash text-red-500 hover:text-red-700"></i>
                                                    </button>
                                                </div>

                                                <!-- QTY + PRICE -->
                                                <div class="mt-2 flex items-center justify-between">

                                                    <!-- QTY -->
                                                    <div x-data="{ quantity: @js($item['quantity']) }"
                                                        class="flex items-center gap-1">

                                                        <button
                                                            class="w-5 h-6 border rounded hover:bg-gray-100"
                                                            @click="
                                                                if (quantity > 1) {
                                                                    quantity--;
                                                                    $wire.updateQuantity({{ $index }}, quantity);
                                                                }
                                                            ">
                                                            −
                                                        </button>

                                                        <input
                                                            type="number"
                                                            min="1"
                                                            x-model.number="quantity"x
                                                            @input.debounce.300ms="$wire.updateQuantity({{ $index }}, quantity)"
                                                            class="w-12 h-6 border rounded text-center font-semibold text-xs">

                                                        <button
                                                            class="w-5 h-6 border rounded hover:bg-gray-100"
                                                            @click="
                                                                quantity++;
                                                                $wire.updateQuantity({{ $index }}, quantity);
                                                            ">
                                                            +
                                                        </button>
                                                    </div>

                                                    <!-- SUBTOTAL -->
                                                    <div class="text-right">
                                                        <div class="text-[10px] text-gray-400">Subtotal</div>
                                                        <div class="font-bold text-gray-900">
                                                            Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                    <!-- SPLIT -->
                                                    @if($splitEnabled)
                                                    <div class="w-24">
                                                        <label class="block text-[10px] text-gray-400 mb-0.5">Split</label>
                                                        <select
                                                            wire:model.live="cart.{{ $index }}.assigned_to"
                                                            class="w-full border rounded px-2 py-1 text-xs">
                                                            @for($i = 1; $i <= ($splitCount ?? 2); $i++)
                                                                <option value="{{ $i }}">Payer {{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>

                                            

                                        </div>
                                    </div>

                                    @endforeach
                                @endif

                            </div>
                        </div>

                        <!-- ================== ACTION BAR (TIDAK SCROLL) ================== -->
                        @if (!empty($cart))
                        <div class="">

                            <div class="bg-yellow border border-gray-100 rounded-lg p-3 shadow-sm">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

                                    <div class="flex items-center gap-3">
                                        <button
                                            x-data
                                            @click="$dispatch('open-print-modal')"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-gray-800 to-gray-900 text-white rounded-lg hover:from-gray-900 hover:to-black shadow-md transform hover:-translate-y-0.5 transition">
                                            <i class="fas fa-print"></i>
                                            <span class="font-semibold">Cetak Bill</span>
                                        </button>

                                        <div class="hidden md:inline-block text-sm text-gray-600 ml-3">{{ count($cart) }} item • Rp{{ number_format($subtotal,0,',','.') }}</div>
                                    </div>

                                    <div class="flex items-center gap-3 flex-wrap">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" wire:model.live="splitEnabled" class="sr-only peer">
                                            <div class="relative w-9 h-5 bg-gray-200 rounded-full
                                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                                after:w-4 after:h-4 after:bg-white after:rounded-full after:transition
                                                peer-checked:bg-blue-600 peer-checked:after:translate-x-4">
                                            </div>
                                            <span class="ml-3 text-sm font-medium">Split Bill?</span>
                                        </label>

                                       <div class="flex items-center gap-3 flex-wrap">
                                         @if($splitEnabled)
                                            <input
                                                type="number"
                                                min="1"
                                                max="10"
                                                wire:model.lazy="splitCount"
                                                class="w-20 border rounded px-2 py-1 text-sm" />

                                            <button
                                                wire:click.prevent="prepareSplit"
                                                class="px-3 inline-flex items-center  bg-gradient-to-r from-gray-800 to-gray-900 text-white rounded-lg hover:from-gray-900 hover:to-black shadow-md transform hover:-translate-y-0.5 transition">
                                                Preview
                                            </button>
                                        @endif
                                       </div>
                                    </div>

                                </div>

                                @if($preparedSplitCount > 0)
                                <div class="flex flex-wrap justify-center gap-2 mt-3">
                                    @for($s = 1; $s <= $preparedSplitCount; $s++)
                                        <a href="{{ route('order.bill') }}?multi=1&split={{ $s }}" target="_blank" class="text-xs px-3 py-1 bg-blue-600 text-white rounded-md text-sm shadow-sm hover:shadow-md transition whitespace-nowrap">
                                            <i class="fas fa-print fa-xs"></i>
                                            Payer {{ $s }}
                                        </a>
                                    @endfor
                                </div>
                                @endif
                            </div>

                        </div>
                        @endif


                           {{-- Modal Cetak Tagihan/Bill --}}
                            <div x-data="{ show: false }" x-on:open-print-modal.window="show = true" x-show="show" x-cloak
                                class="fixed inset-0 z-50 flex items-center justify-center">
                                <!-- Backdrop -->
                                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="show = false"></div>

                                <!-- Modal Box -->
                                <div x-show="show" x-transition
                                    class="relative bg-white w-full max-w-md rounded-xl shadow-2xl p-6">

                                    <!-- Icon -->
                                    <div class="flex justify-center mb-4">
                                        <div
                                            class="w-12 h-12 flex items-center justify-center rounded-full bg-yellow-100 text-yellow-600">
                                            <i class="fas fa-question fa-2x"></i>
                                        </div>
                                    </div>

                                    <!-- Title -->
                                    <h2 class="text-lg font-bold text-center text-gray-800 mb-2">
                                        Konfirmasi Cetak
                                    </h2>

                                    <!-- Desc -->
                                    <p class="text-center text-gray-600 mb-6">
                                        Ingin cetak tagihan baru?
                                    </p>

                                    <!-- Actions -->
                                    <div class="flex justify-center gap-3">
                                        <button @click="show = false"
                                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                            Batal
                                        </button>

                                        <button wire:click="billPayment" wire:loading.attr="disabled" @click="show = false"
                                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                            Cetak
                                        </button>
                                    </div>
                                </div>
                            </div>

                    </div>

                    <!-- Total dan Pembayaran -->
                    <div class="w-full lg:w-1/3 mt-6 lg:mt-0">
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">

                            <div class="space-y-4 mt-4">

                                @if(!empty($cart))
                                {{-- ==================== JENIS PESANAN ==================== --}}
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900">Jenis Pesanan</label>

                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" wire:click="$set('order_type', 'DINE_IN')"
                                            class="py-2 rounded-lg text-sm font-semibold border transition
                                                {{ $order_type === 'DINE_IN' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' }}">
                                            🍽️ Makan di Tempat
                                        </button>

                                        <button type="button" wire:click="$set('order_type', 'TAKEAWAY')"
                                            class="py-2 rounded-lg text-sm font-semibold border transition
                                                {{ $order_type === 'TAKEAWAY' ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' }}">
                                            🥡 Bungkus
                                        </button>
                                    </div>
                                </div>

                                {{-- ==================== NOMOR MEJA ==================== --}}
                    
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900">Meja/Nama</label>
                                    <input type="text" wire:model.live.lazy="desk_number"
                                        class="block w-full p-2 border rounded-lg text-sm bg-gray-50"
                                        placeholder="Contoh: A1, 12, VIP-3">
                                </div>
                                

                                {{-- ==================== CATATAN ==================== --}}
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900">Catatan</label>
                                    <textarea wire:model.defer="note" rows="2"
                                        class="block w-full p-2 border rounded-lg text-sm bg-gray-50"
                                        placeholder="Contoh: Order penting,alergi seafood,pakai tisu, dll..."></textarea>
                                </div>

                                {{-- ==================== MODE PEMBAYARAN ==================== --}}
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900">Mode Pembayaran</label>

                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" wire:click="$set('payment_mode', 'PAY_NOW')"
                                            class="py-2 rounded-lg text-sm font-semibold border transition
                                                {{ $payment_mode === 'PAY_NOW' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-700 border-gray-300' }}">
                                            💳 Bayar Sekarang
                                        </button>

                                        <button type="button" wire:click="$set('payment_mode', 'PAY_LATER')"
                                            class="py-2 rounded-lg text-sm font-semibold border transition
                                                {{ $payment_mode === 'PAY_LATER' ? 'bg-gray-500 text-white border-gray-500' : 'bg-white text-gray-700 border-gray-300' }}">
                                            🕒 Bayar Nanti
                                        </button>
                                    </div>
                                </div>

                                {{-- ==================== METODE BAYAR ==================== --}}
                                @if($payment_mode === 'PAY_NOW')
                                <div>
                                    <select class="block w-full p-2 border rounded-lg text-sm bg-gray-50"
                                        wire:model="payment_method"
                                        class="block w-full border-b text-sm py-2 bg-transparent">
                                        <option value="CASH">Tunai</option>
                                        <option value="QRIS">QRIS</option>
                                        <option value="DEBIT_CARD">Debit</option>
                                        <option value="BCA">BCA</option>
                                        <option value="BRI">BRI</option>
                                        <option value="MANDIRI">Mandiri</option>
                                    </select>
                                </div>
                                @endif

                                {{-- ==================== UANG PELANGGAN ==================== --}}
                                @if($payment_mode === 'PAY_NOW')
                                <div
                                    class="mt-3"
                                    x-data="{
                                        display: '',
                                        raw: @entangle('customerMoney').live,

                                        format(v) {
                                            return v ? new Intl.NumberFormat('id-ID').format(v) : '';
                                        },

                                        init() {
                                            // sync dari backend
                                            this.$watch('raw', v => {
                                                this.display = this.format(v);
                                            });

                                            // WATCH PAYMENT METHOD → LANGSUNG REAKSI
                                            this.$watch('$wire.payment_method', (val) => {
                                                if (val !== 'CASH') {
                                                    // non cash → auto set & lock
                                                    this.raw = @js($total);
                                                    this.display = this.format(this.raw);
                                                } else {
                                                    // cash → buka input
                                                    this.raw = null;
                                                    this.display = '';
                                                }
                                            });
                                        },

                                        onInput(e) {
                                            let v = e.target.value.replace(/[^0-9]/g, '');
                                            this.display = this.format(v);
                                            this.raw = v === '' ? null : parseInt(v);
                                        }
                                    }">
                                    <label class="block mb-2 text-sm font-medium">
                                        Uang Pelanggan
                                    </label>

                                    {{-- INPUT (CASH) --}}
                                    <input
                                        type="text"
                                        x-show="$wire.payment_method === 'CASH'"
                                        x-on:input="onInput($event)"
                                        x-bind:value="display"
                                        inputmode="numeric"
                                        class="block w-full p-2 border rounded-lg text-sm bg-gray-50"
                                        placeholder="Masukkan uang pelanggan"
                                    >

                                    {{-- DISPLAY (NON CASH) --}}
                                    <div
                                        x-show="$wire.payment_method !== 'CASH'"
                                        class="block w-full p-2 border rounded-lg text-sm bg-gray-100 text-gray-700 font-semibold"
                                    >
                                        Rp{{ number_format($total, 0, ',', '.') }}
                                    </div>

                                    <p class="text-xs text-gray-500 mt-1">
                                        <span x-show="$wire.payment_method === 'CASH'">Input manual untuk tunai</span>
                                        <span x-show="$wire.payment_method !== 'CASH'">Otomatis sesuai total</span>
                                    </p>
                                </div>
                                @endif




                                @endif

                                {{-- ==================== RINGKASAN ==================== --}}
                                <dl class="flex justify-between">
                                    <dt>Sub Total</dt>
                                    <dd class="text-gray-900 font-bold">Rp{{ number_format($subtotal,0,',','.') }}</dd>
                                </dl>
                                @if ($settings->is_tax == true)
                                <dl class="flex justify-between">
                                    <dt>Pajak</dt>
                                    <dd class="text-red-500">Rp{{ number_format($tax,0,',','.') }}</dd>
                                </dl>
                                @endif
                                <dl class="flex justify-between font-bold border-t pt-2">
                                    <dt>Total</dt>
                                    <dd class="text-green-500 font-bold">Rp{{ number_format($total,0,',','.') }}</dd>
                                </dl>
                                <dl class="flex justify-between">
                                    <dt>Kembalian</dt>
                                    <dd class="text-blue-500 font-bold">Rp{{ number_format($change,0,',','.') }}</dd>
                                </dl>

                            </div>

                            {{-- ==================== TOMBOL ==================== --}}
                            <div class="flex justify-center mt-6">
                                <button wire:click="processOrder" @if(empty($cart)) disabled @endif
                                    class="bg-green-600 @if(empty($cart)) opacity-50 @endif text-white px-4 py-2 rounded-lg w-full">

                                    @if($selectedUnpaidOrderId)
                                        {{-- Sedang buka order UNPAID --}}
                                        @if($payment_mode === 'PAY_NOW')
                                        Proses
                                        @else
                                        Update Pesanan
                                        @endif
                                    @else
                                        {{-- Order baru --}}
                                        @if($payment_mode === 'PAY_NOW')
                                        Proses
                                        @else
                                        Simpan Pesanan
                                        @endif
                                    @endif

                                </button>
                                <button wire:click="resetCart" @if(empty($cart)) disabled @endif
                                    class="ml-2 bg-red-600 @if(empty($cart)) opacity-50 @endif text-white px-4 py-2 rounded-lg w-full">
                                Clear
                                </button>
                            </div>


                        </div>
                    </div>

                    {{-- ================= UNPAID LIST ================= --}}
                    @if(count($unpaidOrders) === 0)

                    @else
                    <div class="rounded-md border border-red-200 bg-red-50 p-2 shadow-sm text-xs">
                        <h3 class="font-bold text-red-700 mb-2 text-xs">🕒 Belum Dibayar</h3>

                        <div class="space-y-1 max-h-[250px] overflow-y-auto custom-scrollbar">
                            @foreach($unpaidOrders as $order)
                            <button wire:click="selectUnpaidOrder({{ $order->id }})"
                                class="w-full text-left p-2 rounded border bg-white hover:bg-orange-100 transition flex items-center gap-2 text-xs">
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-[11px] truncate"> {{ $order->desk_number ?? '?' }}</div>
                                    <div class="text-[10px] text-gray-500 truncate">{{ $order->order_number }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[11px] font-bold text-orange-600">Rp{{ number_format($order->grandtotal,0,',','.') }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $order->created_at->locale('id')->diffForHumans() }}</div>
                                </div>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif


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
