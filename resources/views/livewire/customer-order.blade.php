<div x-data="{
    showCart: false,
    showNoteModal: false,
    noteProductId: null,
    noteProductName: '',
    noteProductSku: '',
    noteText: '',
    showSuccessModal: false,
    showConfirmModal: false,

    openNoteModal(id, name, sku) {
        this.noteProductId = id;
        this.noteProductName = name;
        this.noteProductSku = sku;
        this.noteText = '';
        this.showNoteModal = true;
    },

    confirmAdd() {
        $wire.addToCart(this.noteProductId, this.noteText);
        this.showNoteModal = false;
    }
}" class="min-h-screen pb-32">

    {{-- ======================== HEADER ======================== --}}
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm">
        <div class="max-w-xl mx-auto px-4 py-3 flex items-center justify-between">

            {{-- Logo & Nama Toko --}}
            <div class="flex items-center gap-2.5">
                @if($storeLogo)
                    <img src="{{ asset('storage/' . $storeLogo) }}" class="w-8 h-8 rounded-full object-cover border border-gray-200" alt="Logo">
                @else
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center">
                        <i class="fas fa-utensils text-white text-xs"></i>
                    </div>
                @endif
                <div>
                    <p class="font-bold text-gray-900 text-sm leading-tight">{{ $storeName }}</p>
                    <p class="text-xs text-gray-400">Meja <span class="font-semibold text-amber-600">{{ $deskNumber }}</span></p>
                </div>
            </div>

            {{-- Cart Icon Button --}}
            <button @click="showCart = true"
                class="relative flex items-center gap-1.5 bg-amber-400 hover:bg-amber-500 transition text-gray-900 font-semibold text-sm px-3 py-2 rounded-full shadow-md active:scale-95">
                <i class="fas fa-shopping-basket text-sm"></i>
                <span>Keranjang</span>
                @if(count($cart) > 0)
                    <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center shadow">
                        {{ collect($cart)->sum('quantity') }}
                    </span>
                @endif
            </button>
        </div>
    </header>

    {{-- ======================== KONTEN UTAMA ======================== --}}
    <main class="max-w-xl mx-auto px-4 pt-4">

        {{-- == SUCCESS ==--}}
        @if($submitted)
        <div class="animate-slide-up flex flex-col items-center justify-center min-h-[60vh] text-center px-6">
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-5 shadow-lg">
                <i class="fas fa-check-circle text-green-500 text-5xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 mb-2">Pesanan Diterima!</h1>
            <p class="text-gray-500 mb-1 text-sm">Nomor pesanan kamu:</p>
            <p class="text-xl font-bold text-amber-600 mb-4">{{ $submittedOrderNumber }}</p>
            <p class="text-gray-400 text-sm max-w-xs">Pesananmu sedang diproses dapur. Pembayaran dilakukan di kasir setelah selesai makan.</p>

            <div class="mt-8 flex flex-col gap-3 w-full max-w-xs">
                <button wire:click="$refresh" onclick="window.location.reload()"
                    class="w-full py-3 bg-amber-400 hover:bg-amber-500 font-bold rounded-xl text-gray-900 shadow-md active:scale-95 transition">
                    <i class="fas fa-plus mr-2"></i>Pesan Lagi
                </button>
            </div>
        </div>
        @else

        {{-- == ERROR ==--}}
        @if($errorMessage)
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm flex gap-2 items-start animate-slide-up">
            <i class="fas fa-exclamation-circle mt-0.5 shrink-0"></i>
            <span>{{ $errorMessage }}</span>
        </div>
        @endif

        {{-- == HERO BANNER == --}}
        <div class="mb-5 bg-gradient-to-r from-amber-400 to-orange-400 rounded-2xl p-4 shadow-md">
            <p class="text-xs font-semibold text-amber-900 uppercase tracking-widest mb-1">Selamat datang 👋</p>
            <h1 class="text-xl font-extrabold text-gray-900 leading-snug">Pilih menu favoritmu,<br>langsung dari meja!</h1>
            <p class="text-xs text-amber-900/70 mt-1">Meja <strong>{{ $deskNumber }}</strong> · Pembayaran di kasir setelah selesai</p>
        </div>

        {{-- == SEARCH == --}}
        <div class="relative mb-4">
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400 text-sm"></i>
            </div>
            <input
                wire:model.defer="search"
                wire:keydown.enter="searchProduct"
                type="search"
                placeholder="Cari menu..."
                class="w-full pl-9 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 shadow-sm"
            >
        </div>

        {{-- == FILTER SKU == --}}
        <div class="flex gap-2 overflow-x-auto pb-2 mb-5 scrollbar-hide no-scrollbar" style="scrollbar-width: none;">
            <button wire:click="setFilterSku('')"
                class="sku-pill shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold border
                {{ $filterSku === '' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                Semua
            </button>
            <button wire:click="setFilterSku('MAKANAN')"
                class="sku-pill shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold border
                {{ $filterSku === 'MAKANAN' ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-600 border-gray-200 hover:bg-orange-50' }}">
                🍜 Makanan
            </button>
            <button wire:click="setFilterSku('MINUMAN')"
                class="sku-pill shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold border
                {{ $filterSku === 'MINUMAN' ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-600 border-gray-200 hover:bg-blue-50' }}">
                🧋 Minuman
            </button>
        </div>

        {{-- == PRODUK LIST == --}}
        <div wire:loading wire:target="searchProduct,setFilterSku" class="space-y-3 animate-pulse">
            @for($i = 0; $i < 4; $i++)
            <div class="bg-white rounded-2xl p-3 flex gap-3 shadow-sm">
                <div class="w-20 h-20 bg-gray-200 rounded-xl shrink-0"></div>
                <div class="flex-1 space-y-2 py-1">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-100 rounded w-1/2"></div>
                    <div class="h-5 bg-gray-200 rounded w-1/3"></div>
                </div>
            </div>
            @endfor
        </div>

        <div wire:loading.remove wire:target="searchProduct,setFilterSku" class="space-y-3">
            @forelse($products as $product)
            @php
                $useStock = $product['use_stock'] ?? true;
                $stock = $product['stock'] ?? 0;
                $outOfStock = $useStock && $stock < 1;
                $inCart = collect($cart)->contains('id', $product['id']);
                $cartQty = collect($cart)->firstWhere('id', $product['id'])['quantity'] ?? 0;
            @endphp

            <div class="product-card bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden
                {{ $outOfStock ? 'opacity-50 pointer-events-none' : '' }}">
                <div class="flex gap-3 p-3">

                    {{-- Gambar --}}
                    <div class="w-20 h-20 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                        <img
                            src="{{ asset('storage/' . ($product['image'] ?? '')) }}"
                            alt="{{ $product['name'] }}"
                            class="w-full h-full object-cover"
                            loading="lazy"
                            onerror="this.src='{{ asset('images/placeholder.png') }}'"
                        >
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0 flex flex-col justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900 text-sm leading-snug line-clamp-2">{{ $product['name'] }}</h3>
                            @if($product['description'])
                                <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $product['description'] }}</p>
                            @endif
                        </div>

                        <div class="flex items-center justify-between mt-2">
                            <span class="font-extrabold text-amber-600 text-sm">
                                Rp{{ number_format($product['price'], 0, ',', '.') }}
                            </span>

                            {{-- Tombol tambah / qty control --}}
                            @if($outOfStock)
                                <span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full font-semibold">Habis</span>
                            @elseif($inCart)
                                {{-- Qty control --}}
                                @php $cartIndex = array_search($product['id'], array_column($cart, 'id')); @endphp
                                <div class="flex items-center gap-2">
                                    <button wire:click="decreaseQty({{ $cartIndex }})"
                                        class="w-7 h-7 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-gray-700 font-bold text-lg leading-none active:scale-90 transition">
                                        −
                                    </button>
                                    <span class="font-bold text-gray-900 text-sm min-w-[1.2rem] text-center">{{ $cartQty }}</span>
                                    <button wire:click="increaseQty({{ $cartIndex }})"
                                        class="w-7 h-7 bg-amber-400 hover:bg-amber-500 rounded-full flex items-center justify-center text-gray-900 font-bold text-lg leading-none active:scale-90 transition">
                                        +
                                    </button>
                                </div>
                            @else
                                <button
                                    @click="openNoteModal({{ $product['id'] }}, @js($product['name']), @js($product['sku']))"
                                    class="flex items-center gap-1.5 bg-gray-900 hover:bg-gray-700 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm active:scale-90 transition">
                                    <i class="fas fa-plus text-[10px]"></i> Tambah
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @empty
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-search text-gray-300 text-2xl"></i>
                </div>
                <p class="text-gray-400 text-sm">Menu tidak ditemukan</p>
            </div>
            @endforelse
        </div>

        @endif {{-- end if submitted --}}
    </main>


    {{-- ======================== MODAL CATATAN PRODUK ======================== --}}
    <div x-show="showNoteModal" x-cloak
        class="fixed inset-0 z-50 flex items-end justify-center"
        @keydown.escape.window="showNoteModal = false">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showNoteModal = false"></div>

        <div x-show="showNoteModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="relative bg-white w-full max-w-xl rounded-t-3xl shadow-2xl p-5 pb-8">

            <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-5"></div>

            <h2 class="font-extrabold text-gray-900 text-lg mb-1" x-text="noteProductName"></h2>
            <p class="text-xs text-gray-400 mb-4">Tambahkan permintaan khusus (opsional)</p>

            <textarea x-model="noteText" rows="3"
                :placeholder="noteProductSku === 'MAKANAN'
                    ? 'Contoh: tidak pedas, pisah kuah, tambah kecap...'
                    : noteProductSku === 'MINUMAN'
                        ? 'Contoh: less sugar, no ice, sedikit es...'
                        : 'Contoh: permintaan custom...'"
                class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none mb-4">
            </textarea>

            <div class="flex gap-3">
                <button @click="showNoteModal = false"
                    class="flex-1 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 active:scale-95 transition">
                    Batal
                </button>
                <button @click="confirmAdd()"
                    class="flex-1 py-3 bg-amber-400 hover:bg-amber-500 text-gray-900 font-bold rounded-xl shadow-md active:scale-95 transition">
                    <i class="fas fa-plus mr-1"></i>Tambahkan
                </button>
            </div>
        </div>
    </div>


    {{-- ======================== CART DRAWER ======================== --}}
    <div x-show="showCart" x-cloak
        class="fixed inset-0 z-50 flex items-end justify-center"
        @keydown.escape.window="showCart = false">

        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showCart = false"></div>

        <div x-show="showCart"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="relative bg-white w-full max-w-xl rounded-t-3xl shadow-2xl flex flex-col"
            style="max-height: 85vh;">

            {{-- Handle --}}
            <div class="shrink-0 px-5 pt-4 pb-3 border-b border-gray-100">
                <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-3"></div>
                <div class="flex items-center justify-between">
                    <h2 class="font-extrabold text-gray-900 text-lg">Keranjang Pesanan</h2>
                    <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">
                        Meja {{ $deskNumber }}
                    </span>
                </div>
            </div>

            {{-- Items --}}
            <div class="flex-1 overflow-y-auto px-5 py-3 space-y-3">

                @if(empty($cart))
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-shopping-basket text-gray-300 text-2xl"></i>
                    </div>
                    <p class="text-gray-400 text-sm">Keranjang masih kosong</p>
                </div>
                @else
                @foreach($cart as $index => $item)
                <div wire:key="cart-customer-{{ $index }}" class="flex gap-3 bg-gray-50 rounded-xl p-3">

                    {{-- Image --}}
                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-200 shrink-0">
                        <img src="{{ asset('storage/' . ($item['image'] ?? '')) }}"
                            alt="{{ $item['name'] }}"
                            class="w-full h-full object-cover"
                            onerror="this.src='{{ asset('images/placeholder.png') }}'">
                    </div>

                    {{-- Details --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 text-sm leading-snug line-clamp-1">{{ $item['name'] }}</p>
                        @if(!empty($item['product_note']))
                            <p class="text-xs text-amber-600 mt-0.5 line-clamp-1 italic">"{{ $item['product_note'] }}"</p>
                        @endif
                        <p class="font-extrabold text-amber-600 text-sm mt-1">
                            Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                        </p>
                    </div>

                    {{-- Qty control --}}
                    <div class="flex flex-col items-center justify-center gap-1 shrink-0">
                        <button wire:click="decreaseQty({{ $index }})"
                            class="w-7 h-7 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-700 font-bold active:scale-90 transition shadow-sm">
                            −
                        </button>
                        <span class="font-bold text-gray-900 text-sm text-center">{{ $item['quantity'] }}</span>
                        <button wire:click="increaseQty({{ $index }})"
                            class="w-7 h-7 bg-amber-400 rounded-full flex items-center justify-center text-gray-900 font-bold active:scale-90 transition shadow-sm">
                            +
                        </button>
                    </div>
                </div>
                @endforeach
                @endif

            </div>

            {{-- Footer Cart --}}
            @if(!empty($cart))
            <div class="shrink-0 border-t border-gray-100 px-5 pt-3 pb-6 space-y-3">

                {{-- Catatan untuk dapur --}}
                <div>
                    <label class="text-xs font-semibold text-gray-500 block mb-1">Catatan untuk dapur (opsional)</label>
                    <textarea
                        wire:model.defer="note"
                        rows="2"
                        placeholder="Contoh: ada yang alergi kacang, tidak pedas..."
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"
                    ></textarea>
                </div>

                {{-- Total --}}
                <div class="flex items-center justify-between py-2 border-t border-dashed border-gray-200">
                    <span class="text-gray-500 text-sm font-medium">Total</span>
                    <span class="text-xl font-extrabold text-gray-900">
                        Rp{{ number_format($total, 0, ',', '.') }}
                    </span>
                </div>

                {{-- Submit Button --}}
                <button
                    wire:click="submitOrder"
                    wire:loading.attr="disabled"
                    @click="showCart = false"
                    class="relative w-full py-4 bg-gradient-to-r from-amber-400 to-orange-400 hover:from-amber-500 hover:to-orange-500
                        text-gray-900 font-extrabold text-base rounded-2xl shadow-lg active:scale-[0.98] transition
                        disabled:opacity-60 disabled:cursor-not-allowed">

                    <span wire:loading.remove wire:target="submitOrder" class="flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        Kirim Pesanan ke Dapur
                    </span>
                    <span wire:loading wire:target="submitOrder" class="flex items-center justify-center gap-2">
                        <i class="fas fa-spinner fa-spin"></i>
                        Mengirim...
                    </span>
                </button>

                <p class="text-center text-xs text-gray-400">
                    Pembayaran dilakukan di kasir setelah selesai makan
                </p>
            </div>
            @endif

        </div>
    </div>

    {{-- Floating cart pill (jika cart tidak kosong dan drawer tutup) --}}
    @if(!empty($cart) && !$submitted)
    <div x-show="!showCart" class="fixed bottom-0 left-0 right-0 z-30 flex justify-center pb-5 px-4 pointer-events-none">
        <button @click="showCart = true"
            class="cart-bar pointer-events-auto bg-gray-900 text-white font-bold px-6 py-3.5 rounded-2xl shadow-2xl
                flex items-center gap-3 active:scale-95 transition">
            <div class="relative">
                <i class="fas fa-shopping-basket text-amber-400"></i>
                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">
                    {{ collect($cart)->sum('quantity') }}
                </span>
            </div>
            <span>Lihat Pesanan</span>
            <span class="ml-auto text-amber-400 font-extrabold">
                Rp{{ number_format($total, 0, ',', '.') }}
            </span>
        </button>
    </div>
    @endif

</div>
