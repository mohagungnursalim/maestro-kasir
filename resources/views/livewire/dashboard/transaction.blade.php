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
                                        <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input type="datetime-local" id="search-date" x-model="date"
                                        x-on:input.debounce.500ms="
                                            let date = new Date($event.target.value);
                                            // Adjust to Asia/Makassar time zone by adding the correct offset
                                            let timeZoneOffset = 8 * 60; // Asia/Makassar is UTC+8
                                            let localTime = new Date(date.getTime() + (timeZoneOffset - date.getTimezoneOffset()) * 60000);
                                            // Format date to 'YYYY-MM-DD'
                                            let formattedDate = localTime.toISOString().split('T')[0];
                                            $wire.set('search', formattedDate)
                                        "
                                        class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            
                                <!-- Input Text with Search Icon -->
                                <div class="relative w-1/2">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input type="text" wire:model.live.debounce.500ms='search' placeholder="Masukan ID Order"
                                        class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            </div>
                            
                            
                            
                            <a wire:loading wire:target='search' class="text-secondary text-sm mb-2">
                                Mencari...
                                <svg role="status"
                                    class="inline w-4 h-4 me-3 text-gray-800 animate-spin" viewBox="0 0 100 101"
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
                        <div
                            class="flex flex-col items-stretch justify-end flex-shrink-0 w-full space-y-2 md:w-auto md:flex-row md:space-y-0 md:items-center md:space-x-3">
                        </div>

                         <!-- Tombol Refresh Page -->
                        <a wire:navigate href="/dashboard/transactions" onclick="showLoading()"
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

                <div wire:init='loadInitialTransactions' class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                        <thead class="text-xs text-white uppercase bg-gray-500">
                            <tr>
                                <th scope="col" class="px-6 py-3">ID Order</th>
                                <th scope="col" class="px-6 py-3">Nama Produk</th>
                                <th scope="col" class="px-6 py-3 text-center">Jumlah</th>
                                <th scope="col" class="px-6 py-3 text-right">Harga</th>
                                <th scope="col" class="px-6 py-3 text-right">Sub Total</th>
                                <th scope="col" class="px-6 py-3 text-center">Tanggal/Jam</th>
                                <th scope="col" class="px-6 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!$loaded)
                                {{-- Skeleton untuk data transaksi saat masih loading --}}
                                @for ($i = 0; $i < 8; $i++) 
                                    <tr class="bg-gray-200 font-bold">
                                        <td class="px-6 py-4" colspan="5">
                                            <div class="h-4 w-20 bg-gray-500 rounded animate-pulse"></div>
                                        </td>
                                        <td class="px-6 py-4"></td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="h-4 w-20 bg-gray-500 rounded animate-pulse"></div>
                                        </td>
                                    </tr>
                
                                    @for ($j = 0; $j < 3; $j++) {{-- 3 transaksi per Order ID --}}
                                        <tr class="bg-white border-b hover:bg-gray-300 text-gray-900">
                                            <td class="px-6 py-4"></td>
                                            <td class="px-6 py-4">
                                                <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <div class="h-4 w-10 bg-gray-300 rounded animate-pulse"></div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <div class="h-4 bg-gray-300 rounded animate-pulse"></div>
                                            </td>
                                            <td class="px-6 py-4"></td>
                                        </tr>
                                    @endfor
                                @endfor
                            @else
                                @forelse ($transactions as $orderId => $orderTransactions)
                                    @php
                                        // Hitung total transaksi per Order ID
                                        $orderTotal = collect($orderTransactions)->sum(fn($transaction) => $transaction->quantity * $transaction->price);
                                    @endphp
                
                                    {{-- Tampilkan ID Order hanya sekali --}}
                                    <tr class="bg-gray-200 font-bold">
                                        <td class="px-6 py-4" colspan="5">ID Order: {{ $orderId }}</td>
                                        <td class="px-6 py-4"></td>
                                        <td class="px-6 py-4 text-right font-bold">Rp{{ number_format($orderTotal, 0, ',', '.') }}</td>
                                    </tr>
                
                                    @foreach ($orderTransactions as $transaction)
                                        <tr class="bg-white border-b hover:bg-gray-300 text-gray-900">
                                            <td class="px-6 py-4"></td>
                                            <td class="px-6 py-4">{{ $transaction->product->name ?? '-' }}</td>
                                            <td class="px-6 py-4 text-center">{{ $transaction->quantity ?? '-' }}</td>
                                            <td class="px-6 py-4 text-right">Rp{{ number_format($transaction->price, 0, ',', '.') ?? '-' }}</td>
                                            <td class="px-6 py-4 text-right">Rp{{ number_format($transaction->quantity * $transaction->price, 0, ',', '.') ?? '-' }}</td>
                                            <td class="px-6 py-4 text-center">{{ $transaction->created_at->format('d-m-Y H:i') ?? '-' }}</td>
                                            <td class="px-6 py-4"></td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center align-middle h-20">
                                            Tidak ada data ditemukan!
                                        </td>
                                    </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
                
                
            {{-- Tombol Load More --}}
            {{-- @if($transactions->count() >= $limit && $totalTransactions > $limit) --}}
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
                    <svg class="inline w-5 h-5 text-white animate-spin ml-2" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 100 8v4a8 8 0 01-8-8z"></path>
                    </svg>
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Loading Progress Tombol Refresh --}}
<script>
    function showLoading() {
        document.getElementById('loading-progress').classList.remove('hidden');
    }
</script>
</div>
