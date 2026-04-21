<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                {{-- Button Add & Search --}}
                <div class="relative bg-white shadow-md sm:rounded-lg">
                    <div
                        class="flex flex-col items-center justify-between p-4 space-y-3 md:flex-row md:space-y-0 md:space-x-4">
                        <div class="md:w-1/2">
                            <div x-data="{ date: '' }" class="relative w-full flex space-x-4">
                                <!-- Input Datetime -->
                                <div class="relative w-1/2">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input type="datetime-local" id="date" name="date" x-model="date"
                                        x-on:input.debounce.500ms="
                                            let date = new Date($event.target.value);
                                            
                                            // Konversi ke waktu Asia/Makassar dengan Intl.DateTimeFormat
                                            let formatter = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Makassar', year: 'numeric', month: '2-digit', day: '2-digit' });
                                            let parts = formatter.formatToParts(date);
                                            
                                            // Format ke 'YYYY-MM-DD'
                                            let formattedDate = `${parts[0].value}-${parts[2].value}-${parts[4].value}`;
                                            $wire.set('search', formattedDate);
                                        "
                                        class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500">

                                </div>

                                <!-- Input Text with Search Icon -->
                                <div class="relative w-1/2">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input type="text" wire:model.live.debounce.500ms='search' id="search" name="search"
                                        placeholder="Masukan kata kunci.."
                                        class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            </div>



                            <a wire:loading wire:target='search' class="text-secondary text-sm mb-2">
                                Mencari...
                                <svg role="status" class="inline w-4 h-4 me-3 text-gray-800 animate-spin"
                                    viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                        fill="#E5E7EB" />
                                    <path
                                        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                        fill="currentColor" />
                                </svg>
                            </a>
                        </div>
                        <div
                            class="flex flex-col items-stretch justify-end flex-shrink-0 w-full space-y-2 md:w-auto md:flex-row md:space-y-0 md:items-center md:space-x-3">
                            
                            @can('Unduh')
                            <!-- Modal Cetak Laporan-->
                            <button data-modal-target="static-modal-queue" data-modal-toggle="static-modal-queue"
                                class="block text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center"
                                type="button">
                                Cetak Laporan
                            </button>
                            @endcan
                            

                            <!-- Tombol Refresh Page -->
                            <a href="{{ route('transactions') }}" onclick="showLoading()"
                                class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                                Refresh
                                <svg id="loading-progress" role="status"
                                    class="hidden inline w-4 h-4 me-3 text-gray-800 animate-spin" viewBox="0 0 100 101"
                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                        fill="#E5E7EB" />
                                    <path
                                        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                        fill="currentColor" />
                                </svg>
                            </a>
                        </div>


                    </div>
                </div>

                <div wire:init='loadInitialTransactions' class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-white uppercase bg-gray-600">
                            <tr>
                                <th class="px-4 py-3">Order / Kasir</th>
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3 text-center">Qty</th>
                                <th class="px-4 py-3 text-right">Harga</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                                <th class="px-4 py-3 text-center">Metode</th>
                                <th class="px-4 py-3 text-right">Sub. Murni</th>
                                <th class="px-4 py-3 text-right">Diskon</th>
                                <th class="px-4 py-3 text-right">S.Tlah Diskon</th>
                                @if ($settings->is_tax == true)
                                <th class="px-4 py-3 text-right">Pajak (PB1)</th>
                                @endif
                                <th class="px-4 py-3 text-right">Uang</th>
                                <th class="px-4 py-3 text-right">Kembali</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-center">Tanggal/Jam</th>
                            </tr>
                        </thead>

                        <tbody>
                       {{-- LOADING --}}
                        @if (!$loaded)
                            @php
                                // Tampilkan skeleton sesuai jumlah data, maksimal 8 (sesuai limit)
                                // Jika data kosong, tampilkan minimal 1 skeleton
                                $skeletonCount = $totalTransactions > 0 ? min($totalTransactions, 8) : 1;
                            @endphp
                            @for ($i = 0; $i < $skeletonCount; $i++)
                                {{-- HEADER ORDER SKELETON --}}
                                <tr class="bg-gray-200 animate-pulse">
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-2">
                                            <div class="h-4 w-24 bg-gray-300 rounded"></div>
                                            <div class="h-4 w-20 bg-gray-300 rounded"></div>
                                        </div>
                                    </td>

                                    <td colspan="4"></td>

                                    <td class="px-4 py-3 text-center">
                                        <div class="h-4 w-16 bg-gray-300 rounded mx-auto"></div>
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        <div class="h-4 w-20 bg-gray-300 rounded ml-auto"></div>
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        <div class="h-4 w-20 bg-gray-300 rounded ml-auto"></div>
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        <div class="h-4 w-20 bg-gray-300 rounded ml-auto"></div>
                                    </td>

                                    @if ($settings->is_tax == true)
                                        <td class="px-4 py-3 text-right">
                                            <div class="h-4 w-20 bg-gray-300 rounded ml-auto"></div>
                                        </td>
                                    @endif

                                    <td class="px-4 py-3 text-right">
                                        <div class="h-4 w-20 bg-gray-300 rounded ml-auto"></div>
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        <div class="h-4 w-20 bg-gray-300 rounded ml-auto"></div>
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        <div class="h-4 w-24 bg-gray-300 rounded ml-auto"></div>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <div class="h-4 w-20 bg-gray-300 rounded mx-auto"></div>
                                    </td>
                                </tr>

                                {{-- DETAIL PRODUK SKELETON (2 baris pura-pura) --}}
                                @for ($d = 0; $d < 2; $d++)
                                    <tr class="bg-white border-b animate-pulse">
                                        <td></td>
                                        <td class="px-4 py-2">
                                            <div class="h-4 w-32 bg-gray-200 rounded"></div>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <div class="h-4 w-6 bg-gray-200 rounded mx-auto"></div>
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <div class="h-4 w-20 bg-gray-200 rounded ml-auto"></div>
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <div class="h-4 w-24 bg-gray-200 rounded ml-auto"></div>
                                        </td>
                                        {{-- 7 base columns after subtotal, plus the 2 new ones (Sub. Murni, S.tlah diskon) = 9. If tax is true, it becomes 10, but since blade is tricky with colspan + conditional, we make it empty tds or use dynamic colspan. --}}
                                        <td colspan="{{ $settings->is_tax ? 10 : 9 }}"></td>
                                    </tr>
                                @endfor
                            @endfor

                        {{-- DATA --}}
                        @else
                            @forelse ($transactions as $orderId => $orderTransactions)

                                @php
                                    $order = $orderTransactions[0];
                                    $subtotalOrder = $order->grand_total - $order->tax + $order->discount;
                                    $discountPercentage = $subtotalOrder > 0 ? round(($order->discount / $subtotalOrder) * 100) : 0;
                                    $base = $order->grand_total - $order->tax;
                                    $taxPercentage = $base > 0 ? round(($order->tax / $base) * 100) : 0;
                                @endphp

                                {{-- HEADER ORDER --}}
                                <tr class="bg-gray-200 font-bold text-gray-800">
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-1">
                                            <span class="bg-green-600 text-white text-xs px-2 py-1 rounded w-fit">
                                                {{ $order->order_number }}
                                            </span>
                                            <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded w-fit">
                                                {{ $order->user_name }}
                                            </span>
                                        </div>
                                    </td>

                                    <td colspan="4"></td>

                                    <td class="px-4 py-3 text-center">{{ strtoupper($order->payment_method) }}</td>

                                    <td class="px-4 py-3 text-right">
                                        Rp{{ number_format($subtotalOrder, 0, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        @if ($discountPercentage)
                                            <span class="text-xs text-gray-600">({{ $discountPercentage }}%)</span>
                                        @endif
                                        Rp{{ number_format($order->discount, 0, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        Rp{{ number_format($subtotalOrder - $order->discount, 0, ',', '.') }}
                                    </td>

                                    @if ($settings->is_tax == true)
                                        <td class="px-4 py-3 text-right">
                                        @if ($taxPercentage)
                                            <span class="text-xs text-gray-600">({{ $taxPercentage }}%)</span>
                                        @endif
                                        Rp{{ number_format($order->tax, 0, ',', '.') }}
                                    </td>
                                    @endif

                                    <td class="px-4 py-3 text-right">
                                        Rp{{ number_format($order->customer_money, 0, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        Rp{{ number_format($order->change, 0, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        Rp{{ number_format($order->grand_total, 0, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        {{ $order->order_date }}
                                    </td>
                                </tr>

                                {{-- DETAIL PRODUK --}}
                                @foreach ($orderTransactions as $trx)
                                    <tr class="bg-white border-b hover:bg-gray-100 text-gray-800">
                                        <td></td>
                                        <td class="px-4 py-2">{{ $trx->product_name }}</td>
                                        <td class="px-4 py-2 text-center">{{ $trx->quantity }}</td>
                                        <td class="px-4 py-2 text-right">
                                            @php
                                                $originalPrice = $trx->product_price ?? $trx->price;
                                                $itemDiscount = max(0, $originalPrice - $trx->price);
                                            @endphp
                                            @if($itemDiscount > 0)
                                                <del class="text-xs text-gray-400 block">Rp{{ number_format($originalPrice, 0, ',', '.') }}</del>
                                                <span class="text-xs text-red-500 block mb-1">-Rp{{ number_format($itemDiscount, 0, ',', '.') }}</span>
                                            @endif
                                            Rp{{ number_format($trx->price, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            Rp{{ number_format($trx->price * $trx->quantity, 0, ',', '.') }}
                                        </td>
                                        <td colspan="{{ $settings->is_tax ? 10 : 9 }}"></td>
                                    </tr>
                                @endforeach

                            @empty
                                <tr>
                                    <td colspan="{{ $settings->is_tax ? 14 : 13 }}" class="text-center py-10 text-gray-400 italic">
                                        Tidak ada data transaksi
                                    </td>
                                </tr>
                            @endforelse
                        @endif
                        </tbody>
                    </table>
                </div>



                {{-- Tombol Load More --}}

                @if(count($transactions->flatten()) >= $limit && $totalTransactions > $limit)

                <div class="mt-4 mb-2 flex justify-center">
                    <!-- Tombol "Tampilkan Lebih" (akan hilang saat loading) -->
                    <button wire:click="loadMore"
                        class="bg-gray-800 text-white px-6 py-2 rounded-full shadow-md hover:bg-gray-700 focus:outline-none focus:ring focus:ring-gray-400 focus:ring-opacity-50"
                        wire:loading.remove wire:target="loadMore">
                        Tampilkan Lebih
                    </button>

                    <!-- Tombol Loading (hanya muncul saat loading) -->
                    <button
                        class="mb-5 bg-gray-800 text-white px-6 py-2 rounded-full shadow-md cursor-not-allowed focus:outline-none focus:ring focus:ring-gray-400 focus:ring-opacity-50"
                        type="button" disabled wire:loading wire:target="loadMore">
                        Memuat..
                       <i class="fas fa-spinner fa-spin"></i>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>




    <!-- Modal Queue -->
    <div wire:ignore id="static-modal-queue" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <div
                    class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Cetak Laporan Transaksi</h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-hide="static-modal-queue">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 md:p-5 space-y-4" x-data>
                    <div class="p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                        <span class="font-medium">Info:</span>
                        Generate laporan transaksi berdasarkan rentang tanggal yang dipilih.
                        Proses pembuatan laporan dilakukan secara <span class="font-bold text-red-500">Asinkron (Queued Processing)</span>.
                        Laporan akan diproses di latar belakang dan dapat diakses melalui halaman
                        <a wire:navigate href="/dashboard/reports" class="text-blue-600 dark:text-blue-500 hover:underline">Laporan</a>
                        setelah selesai.
                    </div>

                    <label for="start_date_queue" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input type="date" id="start_date_queue" name="start_date_queue" wire:model="startDate"
                        class="w-full border border-gray-300 p-2 rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-800">

                    <label for="end_date_queue" class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                    <input type="date" id="end_date_queue" name="end_date_queue" wire:model="endDate"
                        class="w-full border border-gray-300 p-2 rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-800">
                    <p x-show="!$wire.isDateRangeWithinYear" class="text-sm text-red-600 mt-2">Rentang tanggal tidak boleh lebih dari 1 tahun.</p>
                </div>

                <div class="flex items-center justify-center p-4 md:p-5 border-t border-gray-200 rounded-b">
                    <!-- Tombol Excel -->
                    <button type="button" wire:loading.remove wire:click="queueReport('excel')"
                        class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                        Export Excel
                    </button>

                    <button type="button" wire:loading wire:target="queueReport('excel')"
                        class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        Export Excel
                        <i class="fas fa-spinner fa-spin"></i>
                    </button>

                    <!-- Tombol PDF -->
                    <button type="button" wire:loading.remove wire:target="queueReport('pdf')"
                        wire:click="queueReport('pdf')"
                        class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:text-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                        Export PDF
                    </button>

                    <button type="button" wire:loading wire:target="queueReport('pdf')"
                        class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:text-blue-700">
                        Export PDF
                        <i class="fas fa-spinner fa-spin"></i>
                    </button>
                </div>

            </div>
        </div>
    </div>




    {{-- Loading Progress Tombol Refresh --}}
    <script>
        function showLoading() {
            document.getElementById('loading-progress').classList.remove('hidden');
        }

    </script>

    {{-- Redirect setelah 3 detik --}}
    <script>
        document.addEventListener('livewire:navigated', () => {
            Livewire.on('notify', () => {
                let countdown = 3;
                Swal.fire({
                    title: "Laporan Sedang Diproses!",
                    html: `Anda akan diarahkan ke halaman unduh laporan dalam <br><b>${countdown}</b> detik.`,
                    icon: "success",
                    timer: 3000, // 3 detik
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        let timerInterval = setInterval(() => {
                            countdown--;
                            Swal.getHtmlContainer().innerHTML =
                                `Anda akan diarahkan ke halaman unduh laporan dalam <br><b>${countdown}</b> detik.`;

                            if (countdown <= 0) {
                                clearInterval(timerInterval);
                            }
                        }, 1000);
                    }
                });

                // Tambahkan delay manual sebelum redirect
                setTimeout(() => {
                    window.location.href = "/dashboard/reports";
                }, 3000); // 3 detik
            });

        });

    </script>




</div>
