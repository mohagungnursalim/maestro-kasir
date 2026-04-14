<div class="py-12">
    @can('Lihat')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        
        <div wire:init="loadInitialStats" class="bg-white shadow rounded-lg p-6 mb-6">
            <!-- Filter -->
            <div class="mb-4">
                <label for="filterType" class="block text-gray-700 font-semibold mb-2">Filter Waktu:</label>
                <select wire:model.live="filterType" id="filterType" class="px-4 py-2 border rounded w-full">
                    <option value="today">Hari Ini</option>
                    <option value="week">Minggu Ini</option>
                    <option value="month">Bulan Ini</option>
                    <option value="year">Tahun Ini</option>
                </select>
            </div>
        
            <!-- Statistik -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 @role('admin|owner') xl:grid-cols-8 @endrole gap-6">
                <!-- Total Order -->
                <div class="p-4 bg-white shadow rounded-lg border border-gray-200">
                    @if(!$loaded)
                        <div class="h-4 bg-gray-200 rounded-full w-24 mb-3 animate-pulse"></div>
                        <div class="h-8 bg-gray-200 rounded-full w-20 animate-pulse"></div>
                    @else
                        <h3 class="text-lg font-semibold text-gray-700">Total Order</h3>
                        <p class="text-2xl font-bold text-gray-600">{{ number_format($totalOrders, 0, ',', '.') }}</p>
                    @endif
                </div>
        
                <!-- Omzet Penjualan (murni dari transaksi) -->
                <div class="p-4 bg-green-50 shadow rounded-lg border border-green-200 flex flex-col justify-between">
                    @if(!$loaded)
                        <div class="h-4 bg-green-200 rounded-full w-36 mb-3 animate-pulse"></div>
                        <div class="h-8 bg-green-200 rounded-full w-40 animate-pulse"></div>
                        <div class="h-10 bg-green-200 rounded w-full mt-3 animate-pulse"></div>
                    @else
                        <div>
                            <div class="flex items-start justify-between gap-1">
                                <h3 class="text-lg font-semibold text-green-700">Omzet Penjualan</h3>
                                @if ($omzetGrowth !== null)
                                    @php $isUp = $omzetGrowth >= 0; @endphp
                                    <span class="inline-flex items-center gap-1 text-xs font-bold px-1.5 py-0.5 rounded-full {{ $isUp ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                        <i class="fas {{ $isUp ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i> {{ abs($omzetGrowth) }}%
                                    </span>
                                @endif
                            </div>
                            <p class="text-2xl font-bold text-green-600">Rp{{ number_format($totalOmzet, 0, ',', '.') }}</p>
                            @if ($yesterdayOmzet > 0)
                                <p class="text-xs text-green-500 mt-0.5">vs Rp{{ number_format($yesterdayOmzet, 0, ',', '.') }} sblmnya</p>
                            @endif
                        </div>
                        <div class="mt-3 flex justify-between items-center text-sm border-t border-green-200 pt-2">
                            <div class="flex flex-col">
                                <span class="text-green-700 text-xs">Tunai</span>
                                <span class="font-semibold text-green-800">Rp{{ number_format($totalTunai, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex flex-col text-right">
                                <span class="text-green-700 text-xs">QRIS</span>
                                <span class="font-semibold text-green-800">Rp{{ number_format($totalQris, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Uang Keuntungan (Omzet - Pengeluaran) -->
                <div class="p-4 bg-emerald-50 shadow rounded-lg border border-emerald-200 flex flex-col justify-between">
                    @if(!$loaded)
                        <div class="h-4 bg-emerald-200 rounded-full w-36 mb-3 animate-pulse"></div>
                        <div class="h-8 bg-emerald-200 rounded-full w-40 animate-pulse"></div>
                        <div class="h-3 bg-emerald-200 rounded-full w-32 mt-auto animate-pulse"></div>
                    @else
                        <div>
                            <h3 class="text-lg font-semibold text-emerald-700">Uang Keuntungan</h3>
                            <p class="text-2xl font-bold text-emerald-600">Rp{{ number_format($uangKeuntungan, 0, ',', '.') }}</p>
                        </div>
                        <p class="text-xs text-emerald-500 mt-2 border-t border-emerald-200 pt-1">Omzet − Pengeluaran</p>
                    @endif
                </div>

                <!-- Total Top Up Kas -->
                <div class="p-4 bg-white shadow rounded-lg border border-gray-200">
                    @if(!$loaded)
                        <div class="h-4 bg-gray-200 rounded-full w-28 mb-3 animate-pulse"></div>
                        <div class="h-8 bg-green-200 rounded-full w-36 animate-pulse"></div>
                        <div class="h-3 bg-gray-200 rounded-full w-24 mt-2 animate-pulse"></div>
                    @else
                        <h3 class="text-lg font-semibold text-gray-700">Top Up Kas</h3>
                        <p class="text-2xl font-bold text-green-400">Rp{{ number_format($totalTopUps, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 mt-1">Pemasukan non-penjualan</p>
                    @endif
                </div>

                <!-- Pengeluaran Kas -->
                <div class="p-4 bg-red-50 shadow rounded-lg border border-red-200">
                    @if(!$loaded)
                        <div class="h-4 bg-red-200 rounded-full w-24 mb-3 animate-pulse"></div>
                        <div class="h-8 bg-red-200 rounded-full w-32 animate-pulse"></div>
                        <div class="h-3 bg-red-200 rounded-full w-28 mt-2 animate-pulse"></div>
                    @else
                        <h3 class="text-lg font-semibold text-red-700">Kas Keluar</h3>
                        <p class="text-2xl font-bold text-red-600">Rp{{ number_format($totalExpenses, 0, ',', '.') }}</p>
                        <p class="text-xs text-red-400 mt-1">Pengeluaran & Belanja</p>
                    @endif
                </div>
        
                <!-- Total Produk Terjual -->
                <div class="p-4 bg-white shadow rounded-lg border border-gray-200">
                    @if(!$loaded)
                        <div class="h-4 bg-gray-200 rounded-full w-32 mb-3 animate-pulse"></div>
                        <div class="h-8 bg-blue-200 rounded-full w-16 animate-pulse"></div>
                    @else
                        <h3 class="text-lg font-semibold text-gray-700">Produk Terjual</h3>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($totalProductsSold, 0, ',', '.') }}</p>
                    @endif
                </div>

                @role('admin|owner')
                <!-- Total Dilihat (Page Views) -->
                <div class="p-4 bg-white shadow rounded-lg border border-gray-200">
                    @if(!$loaded)
                        <div class="h-4 bg-gray-200 rounded-full w-28 mb-3 animate-pulse"></div>
                        <div class="h-8 bg-gray-200 rounded-full w-20 animate-pulse"></div>
                        <div class="h-3 bg-gray-200 rounded-full w-32 mt-2 animate-pulse"></div>
                    @else
                        <h3 class="text-lg font-semibold text-gray-700">Total Dilihat</h3>
                        <p class="text-2xl font-bold text-gray-600">{{ number_format($totalPageViews ?? 0, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400 mt-1">Total kunjungan halaman</p>
                    @endif
                </div>

                <!-- Pengunjung Unik (Unique IP) -->
                <div class="p-4 bg-white shadow rounded-lg border border-gray-200">
                    @if(!$loaded)
                        <div class="h-4 bg-gray-200 rounded-full w-36 mb-3 animate-pulse"></div>
                        <div class="h-8 bg-gray-200 rounded-full w-16 animate-pulse"></div>
                        <div class="h-3 bg-gray-200 rounded-full w-28 mt-2 animate-pulse"></div>
                    @else
                        <h3 class="text-lg font-semibold text-gray-700">Pengunjung Unik</h3>
                        <p class="text-2xl font-bold text-gray-600">{{ number_format($totalUniqueVisitors ?? 0, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400 mt-1">Berdasarkan IP unik</p>
                    @endif
                </div>
                @endrole
            </div>

            {{-- ── AOV + Avg Diskon row ─────────────────────────────────────────── --}}
            @if ($loaded)
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Average Order Value -->
                <div class="flex items-center gap-4 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                    <div class="text-3xl text-purple-600"><i class="fas fa-shopping-cart"></i></div>
                    <div>
                        <p class="text-xs text-purple-500 font-semibold uppercase tracking-wide">Rata-rata / Transaksi (AOV)</p>
                        <p class="text-xl font-bold text-purple-700">Rp{{ number_format($avgOrderValue, 0, ',', '.') }}</p>
                    </div>
                </div>
                <!-- Rata-rata Diskon -->
                <div class="flex items-center gap-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="text-3xl text-amber-500"><i class="fas fa-tag"></i></div>
                    <div>
                        <p class="text-xs text-amber-500 font-semibold uppercase tracking-wide">Rata-rata Diskon / Order</p>
                        <p class="text-xl font-bold text-amber-700">Rp{{ number_format($avgDiscount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    
        @livewire('dashboard.monthly-turnover-chart')

        {{-- ── Peak Hour + Payment Split ────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-3">
            @livewire('dashboard.peak-hour-chart')
            @livewire('dashboard.payment-split-chart')
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-2">

            @livewire('dashboard.product-sales-chart')

            @livewire('dashboard.stock-warning')

        </div>
        
    </div>
    @endcan
</div>
