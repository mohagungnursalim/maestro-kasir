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
        
            {{-- Statistik --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 @role('admin|owner') xl:grid-cols-9 @endrole gap-6">
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

                {{-- Point 1: Piutang / Belum Lunas --}}
                <div class="p-4 bg-orange-50 shadow rounded-lg border border-orange-300 flex flex-col justify-between">
                    @if(!$loaded)
                        <div class="h-4 bg-orange-200 rounded-full w-28 mb-3 animate-pulse"></div>
                        <div class="h-8 bg-orange-200 rounded-full w-32 animate-pulse"></div>
                        <div class="h-3 bg-orange-200 rounded-full w-24 mt-2 animate-pulse"></div>
                    @else
                        <div>
                            <h3 class="text-lg font-semibold text-orange-700 flex items-center gap-1">
                                Piutang
                                @if ($totalUnpaidOrders > 0)
                                    <span class="ml-1 inline-flex items-center justify-center text-xs font-bold bg-orange-500 text-white rounded-full w-5 h-5">{{ $totalUnpaidOrders }}</span>
                                @endif
                            </h3>
                            <p class="text-2xl font-bold {{ $totalUnpaidAmount > 0 ? 'text-orange-600' : 'text-gray-400' }}">
                                Rp{{ number_format($totalUnpaidAmount, 0, ',', '.') }}
                            </p>
                        </div>
                        <p class="text-xs text-orange-400 mt-2 border-t border-orange-200 pt-1">
                            {{ $totalUnpaidOrders }} order belum lunas
                        </p>
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

            {{-- ── Point 2: Rasio Tipe Order ──────────────────────────────────────── --}}
            @if ($loaded)
            @php
                $typeLabels = [
                    'DINE_IN'   => ['label' => 'Dine-in',   'color' => 'blue',   'icon' => 'fa-utensils'],
                    'TAKE_AWAY' => ['label' => 'Take Away',  'color' => 'violet', 'icon' => 'fa-bag-shopping'],
                    'GOFOOD'    => ['label' => 'GoFood',     'color' => 'green',  'icon' => 'fa-motorcycle'],
                    'GRABFOOD'  => ['label' => 'GrabFood',   'color' => 'emerald','icon' => 'fa-motorcycle'],
                    'MAXIM'     => ['label' => 'Maxim',      'color' => 'yellow', 'icon' => 'fa-motorcycle'],
                ];
                $colorMap = [
                    'blue'    => ['bar' => 'bg-blue-500',    'text' => 'text-blue-700',    'bg' => 'bg-blue-50',    'border' => 'border-blue-200'],
                    'violet'  => ['bar' => 'bg-violet-500',  'text' => 'text-violet-700',  'bg' => 'bg-violet-50',  'border' => 'border-violet-200'],
                    'green'   => ['bar' => 'bg-green-500',   'text' => 'text-green-700',   'bg' => 'bg-green-50',   'border' => 'border-green-200'],
                    'emerald' => ['bar' => 'bg-emerald-500', 'text' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                    'yellow'  => ['bar' => 'bg-yellow-400',  'text' => 'text-yellow-700',  'bg' => 'bg-yellow-50',  'border' => 'border-yellow-200'],
                ];
            @endphp
            @if (!empty($orderTypeSplit))
            <div class="mt-4 p-4 bg-white border border-gray-200 rounded-lg shadow">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">
                    <i class="fas fa-chart-pie mr-1 text-indigo-500"></i> Rasio Tipe Pesanan
                </h3>
                <div class="space-y-2">
                    @foreach ($orderTypeSplit as $type => $data)
                        @php
                            $meta  = $typeLabels[$type] ?? $typeLabels['other'];
                            $clr   = $colorMap[$meta['color']] ?? $colorMap['gray'];
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-24 shrink-0 flex items-center gap-1 {{ $clr['text'] }} text-xs font-semibold">
                                <i class="fas {{ $meta['icon'] }} w-4 text-center"></i>
                                {{ $meta['label'] }}
                            </div>
                            <div class="flex-1 bg-gray-100 rounded-full h-3 overflow-hidden">
                                <div class="h-3 rounded-full transition-all duration-500 {{ $clr['bar'] }}"
                                    style="width: {{ $data['percentage'] }}%"></div>
                            </div>
                            <div class="w-32 shrink-0 text-right text-xs text-gray-600">
                                <span class="font-bold">{{ $data['percentage'] }}%</span>
                                <span class="text-gray-400">({{ $data['count'] }} order)</span>
                            </div>
                            <div class="w-28 shrink-0 text-right text-xs font-semibold {{ $clr['text'] }}">
                                Rp{{ number_format($data['omzet'], 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
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

        {{-- ── Point 5: Transaksi Terbaru ────────────────────────────────────── --}}
        @if ($loaded && count($recentTransactions))
        @php
            $methodColor = [
                'CASH'  => 'bg-green-100 text-green-800',
                'TUNAI' => 'bg-green-100 text-green-800',
                'QRIS'  => 'bg-blue-100 text-blue-800',
            ];
            $typeIcon = [
                'dine_in'   => ['icon' => 'fa-utensils',    'label' => 'Dine-in',  'color' => 'text-blue-500'],
                'take_away' => ['icon' => 'fa-bag-shopping','label' => 'Take Away','color' => 'text-violet-500'],
                'gofood'    => ['icon' => 'fa-motorcycle',  'label' => 'GoFood',   'color' => 'text-green-600'],
                'grabfood'  => ['icon' => 'fa-motorcycle',  'label' => 'GrabFood', 'color' => 'text-emerald-600'],
                'maxim'     => ['icon' => 'fa-motorcycle',  'label' => 'Maxim',    'color' => 'text-yellow-600'],
            ];
        @endphp
        <div class="bg-white shadow rounded-lg p-5 mb-3">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">
                <i class="fas fa-clock-rotate-left mr-1 text-slate-500"></i>
                10 Transaksi Terbaru
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead>
                        <tr class="text-xs uppercase text-gray-500 border-b">
                            <th class="pb-2 pr-4">No. Order</th>
                            <th class="pb-2 pr-4">Tipe</th>
                            <th class="pb-2 pr-4">Kasir</th>
                            <th class="pb-2 pr-4 text-center">Metode</th>
                            <th class="pb-2 pr-4 text-right">Diskon</th>
                            <th class="pb-2 text-right">Total</th>
                            <th class="pb-2 pl-4 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recentTransactions as $trx)
                            @php
                                $method     = strtoupper($trx['payment_method'] ?? '');
                                $methodCls  = $methodColor[$method] ?? 'bg-gray-100 text-gray-800';
                                $tIcon      = $typeIcon[$trx['order_type'] ?? ''] ?? ['icon' => 'fa-circle-question', 'label' => $trx['order_type'] ?? '-', 'color' => 'text-gray-400'];
                                $kasir      = $trx['user']['name'] ?? '-';
                                $createdAt  = \Carbon\Carbon::parse($trx['created_at'])->setTimezone('Asia/Makassar');
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-2 pr-4 font-mono font-semibold text-gray-800 text-xs">
                                    {{ $trx['order_number'] }}
                                </td>
                                <td class="py-2 pr-4">
                                    <span class="flex items-center gap-1 text-xs {{ $tIcon['color'] }}">
                                        <i class="fas {{ $tIcon['icon'] }}"></i>
                                        {{ $tIcon['label'] }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4 text-xs text-gray-500">{{ $kasir }}</td>
                                <td class="py-2 pr-4 text-center">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $methodCls }}">
                                        {{ $method ?: '-' }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4 text-right text-xs {{ $trx['discount'] > 0 ? 'text-red-500' : 'text-gray-400' }}">
                                    @if ($trx['discount'] > 0)
                                        −Rp{{ number_format($trx['discount'], 0, ',', '.') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-2 text-right font-bold text-gray-800">
                                    Rp{{ number_format($trx['grandtotal'], 0, ',', '.') }}
                                </td>
                                <td class="py-2 pl-4 text-right text-xs text-gray-400 whitespace-nowrap">
                                    {{ $createdAt->format('H:i') }}
                                    <span class="block text-gray-300">{{ $createdAt->format('d M') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        
    </div>
    @endcan
</div>
