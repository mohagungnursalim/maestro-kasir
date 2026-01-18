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
                                <svg class="w-4 h-4 text-gray-500" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>

                            <!-- Input -->
                            <input wire:model.defer="search" type="search" id="search" name="search"
                                class="bg-white block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Cari produk.." />

                            <!-- Button Normal -->
                            <button type="submit"
                                wire:loading.remove
                                wire:target="searchProduct"
                                class="text-gray-900 absolute end-2.5 bottom-2.5 bg-yellow-300 hover:bg-yellow-400 focus:ring-4 focus:outline-none focus:ring-yellow-200 font-medium rounded-lg text-sm px-4 py-2 flex items-center gap-2">
                                Cari
                            </button>

                            <!-- Button Loading -->
                            <button type="button" disabled
                                wire:loading
                                wire:target="searchProduct"
                                class="text-gray-900 absolute end-2.5 bottom-2.5 bg-yellow-300 opacity-70 font-medium rounded-lg text-sm px-4 py-2 flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i>
                            </button>

                        </div>
                    </form>
                </div>

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
                                                    <button wire:click="addToCart({{ $product->id }})"
                                                        wire:loading.remove wire:target="addToCart({{ $product->id }})"
                                                        onclick="playSelectSound()" @if($product->stock < 1) disabled
                                                            @endif class="w-full bg-gray-900 text-white py-0.5 px-2 rounded text-xs hover:bg-gray-800 transition flex items-center justify-center gap-1 disabled:bg-gray-300 disabled:cursor-not-allowed">

                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                            </svg>
                                                            <span>Tambah</span>
                                                    </button>

                                                    <button wire:loading wire:target="addToCart({{ $product->id }})"
                                                        class="w-full bg-gray-900 text-white py-0.5 px-2 rounded text-xs flex items-center justify-center gap-1 cursor-wait">
                                                        <i class="fas fa-spinner fa-spin"></i>

                                                    </button>
                                                    @else
                                                    <button disabled class="w-full bg-green-100 text-green-800 py-0.5 px-2 rounded text-xs flex items-center justify-center gap-1 cursor-not-allowed">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        <span>Ditambahkan</span>
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





                <!-- Keranjang Belanja -->
                <h2 class="text-xl font-semibold text-gray-900 sm:text-2xl mt-6">Pesanan</h2>
                <div class="mt-6 sm:mt-8 md:gap-6 lg:flex lg:items-start">
                    
                 <!-- Daftar Item Pesanan -->
                <div class="w-full lg:w-2/3 max-h-[60vh] overflow-y-auto custom-scrollbar">

                    <div class="space-y-2">

                        @if (empty($cart))
                            <div class="text-center mt-9 text-sm text-gray-500">
                                Pesanan Kosong <i class="far fa-list-alt"></i>
                            </div>
                        @else

                        @foreach ($cart as $index => $item)

                        <div class="border rounded-md bg-white px-3 py-2 text-sm">

                            <div class="flex items-center gap-2">

                                <!-- Nama & Batal -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-center gap-2">
                                        <span class="font-medium text-gray-900 truncate">
                                            {{ $item['name'] }}
                                        </span>

                                        <!-- Hapus -->
                                        <button wire:click="removeFromCart({{ $index }})"
                                            wire:loading.remove
                                            wire:target="removeFromCart({{ $index }})"
                                            onclick="event.stopPropagation(); playSelectSound()"
                                            class="text-xs text-red-600 hover:underline">
                                            Batal
                                        </button>

                                        <span wire:loading wire:target="removeFromCart({{ $index }})"
                                            class="text-xs text-gray-400 animate-pulse">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </span>
                                    </div>
                                </div>

                                <!-- Qty Control -->
                                <div x-data="{ quantity: {{ $item['quantity'] }} }" class="flex items-center gap-1">

                                    {{-- Button (-) --}}
                                    <button type="button"
                                        class="w-6 h-6 flex items-center justify-center border rounded bg-gray-100 hover:bg-gray-200"
                                        x-on:click="
                                            if (quantity > 1) {
                                                quantity--;
                                                clearTimeout(window.qtyTimeout);
                                                window.qtyTimeout = setTimeout(() => {
                                                    $wire.updateQuantity({{ $index }}, quantity);
                                                }, 500);
                                            }
                                        ">
                                        −
                                    </button>

                                    <input type="number" min="1" class="w-14 h-7 text-center text-xs font-semibold border border-gray-300 rounded text-gray-900 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            x-model.number="quantity"
                                            x-on:input.debounce.400ms="$wire.updateQuantity({{ $index }}, quantity)">

                                    {{-- Button (+) --}}
                                    <button type="button"
                                        class="w-6 h-6 flex items-center justify-center border rounded bg-gray-100 hover:bg-gray-200"
                                        x-on:click="
                                            quantity++;
                                            clearTimeout(window.qtyTimeout);
                                            window.qtyTimeout = setTimeout(() => {
                                                $wire.updateQuantity({{ $index }}, quantity);
                                            }, 500);
                                        ">
                                        +
                                    </button>

                                </div>

                                <!-- Subtotal -->
                                <div class="w-24 text-right font-semibold text-gray-900 text-xs">
                                    Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                </div>

                            </div>

                        </div>

                        @endforeach
                        @endif

                    </div>

                    <!-- Tombol Cetak -->
                    @if (!empty($cart))
                    <div class="flex justify-center mt-4">
                        <button x-data @click="$dispatch('open-print-modal')"
                            class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition">
                            Cetak Tagihan
                        </button>

                    </div>
                    @endif

                        {{-- Modal Cetak Tagihan/Bill --}}
                        <div x-data="{ show: false }" x-on:open-print-modal.window="show = true" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
                            <!-- Backdrop -->
                            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="show = false"></div>

                            <!-- Modal Box -->
                            <div x-show="show" x-transition class="relative bg-white w-full max-w-md rounded-xl shadow-2xl p-6">
                               
                                <!-- Icon -->
                                <div class="flex justify-center mb-4">
                                    <div class="w-12 h-12 flex items-center justify-center rounded-full bg-yellow-100 text-yellow-600">
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
                                    <button 
                                        @click="show = false"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
                                    >
                                        Batal
                                    </button>

                                    <button 
                                        wire:click="billPayment"
                                        wire:loading.attr="disabled"
                                        @click="show = false"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                                    >
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

                                {{-- Metode Pembayaran --}}
                                <div x-data="{ 
                                        paymentMethod: @entangle('payment_method'), 
                                        isCash() { return this.paymentMethod === 'cash'; }
                                    }" x-init="$watch('paymentMethod', value => {
                                        if (value !== 'cash') {
                                            @this.set('customerMoney', 0);
                                        }
                                    })">

                                    <!-- Pilih Metode Pembayaran -->
                                    <div>
                                        <select x-model="paymentMethod" wire:model="payment_method" id="paymentMethod"
                                            name="paymentMethod" :disabled="@json(empty($cart))" class="block py-2.5 px-0 w-full text-sm text-gray-500 bg-transparent border-0 border-b-2 
                                                   border-gray-200 appearance-none focus:outline-none focus:ring-0 
                                                   focus:border-gray-200 peer disabled:cursor-not-allowed">

                                            <option value="CASH">Tunai</option>
                                            <option value="DEBIT_CARD">Kartu Debit</option>


                                            <option value="QRIS">QRIS</option>



                                            <optgroup label="Transfer Bank">
                                                <option value="BCA">BCA</option>
                                                <option value="BRI">BRI</option>
                                                <option value="MANDIRI">Mandiri</option>
                                                <option value="BNI">BNI</option>
                                                <option value="lainnya">Lainnya</option>
                                            </optgroup>

                                        </select>
                                    </div>

                                    <!-- Input Uang Pelanggan -->
                                    <div class="mt-3" x-data="{
                                                display: '',
                                                raw: @entangle('customerMoney').live,
                                                _timer: null,

                                                format(n) {
                                                    if (!n) return '';
                                                    return new Intl.NumberFormat('id-ID').format(n);
                                                },

                                                init() {
                                                    // Inisialisasi format saat load
                                                    if(this.raw) this.display = this.format(this.raw);

                                                    // Listener untuk reset
                                                    window.addEventListener('resetCustomerMoneyInput', () => {
                                                        this.display = '';
                                                        this.raw = 0;
                                                    });
                                                    
                                                    // Opsional: Watch jika Livewire mengupdate nilai dari server
                                                    this.$watch('raw', (value) => {
                                                        // Cek agar tidak konflik saat mengetik
                                                        if (document.activeElement !== this.$refs.input) {
                                                            this.display = this.format(value);
                                                        }
                                                    });
                                                },

                                                onInput(e) {
                                                    let value = e.target.value.replace(/[^0-9]/g, '');
                                                    this.display = this.format(value);

                                                    clearTimeout(this._timer);
                                                    this._timer = setTimeout(() => {
                                                        this.raw = value === '' ? 0 : parseInt(value);
                                                    }, 500);
                                                }
                                            }">
                                        <label class="block mb-2 text-sm font-medium text-gray-900">
                                            Uang Pelanggan
                                        </label>

                                        <input type="text" x-ref="input" x-on:input="onInput($event)"
                                            x-on:focus="if(display === '0') display = ''" x-bind:value="display"
                                            :disabled="@json(empty($cart))"
                                            class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500 disabled:cursor-not-allowed"
                                            placeholder="Masukkan uang pelanggan...">
                                    </div>

                                </div>

                                {{-- Subtotal --}}
                                <dl class="flex justify-between">
                                    <dt class="text-gray-500">Sub Total</dt>
                                    @if (!empty($cart)) <span
                                        class="font-bold p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs">Rp{{ number_format($subtotal, 2, ',', '.') }}</span>
                                    @endif
                                </dl>

                                <!-- Pajak dalam Rupiah -->
                                @if ($settings->is_tax)
                                <dl class="flex justify-between mt-3">
                                    <dt class="text-gray-500">Pajak PB1 (<span>{{ $tax_percentage }}</span>%)</dt>

                                    @if (!empty($cart) && isset($tax))
                                    <span
                                        class="font-bold p-2 text-red-500 border border-gray-300 rounded-lg bg-gray-50 text-xs">
                                        Rp{{ number_format($tax, 2, ',', '.') }}
                                    </span>
                                    @endif
                                </dl>
                                @endif

                                {{-- Total Bayar --}}
                                <dl class="flex justify-between border-t pt-2">
                                    <dt class="font-bold text-gray-900">Total Bayar</dt>
                                    @if (!empty($cart)) <span
                                        class="font-bold p-2 text-gray-900 border border-green-300 rounded-lg bg-green-50 text-xs">Rp{{ number_format($total, 2, ',', '.') }}</span>
                                    @endif
                                </dl>

                                {{-- Uang Kembali --}}
                                <dl class="flex justify-between">
                                    <dt class="font-bold text-gray-900">Uang Kembali</dt>
                                    @if (!empty($cart)) <span
                                        class="font-bold p-2 text-gray-900 border border-blue-300 rounded-lg bg-blue-50 text-xs">Rp{{ number_format($change, 2, ',', '.') }}</span>
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
                                    class="focus:outline-none text-white bg-green-500 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                    @disabled(empty($cart))>
                                    Proses
                                </button>

                                @endif

                                <!-- Tombol Loading saat Memproses -->
                                <button disabled wire:loading wire:target="processOrder"
                                    class="focus:outline-none text-white bg-green-500 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Memproses..
                                    <i class="fas fa-spinner fa-spin"></i>   
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
                                    <i class="fas fa-spinner fa-spin"></i>                     
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
