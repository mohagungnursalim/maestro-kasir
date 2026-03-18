<div class="py-12">
    @can('Lihat')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        
        <div class="bg-white shadow rounded-lg p-6 mb-6">
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 @role('admin|owner') xl:grid-cols-5 @endrole gap-6">
                <!-- Total Order -->
                <div class="p-4 bg-white shadow rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-700">Total Order</h3>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($totalOrders, 0, ',', '.') }}</p>
                </div>
        
                <!-- Subtotal -->
                <div class="p-4 bg-white shadow rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-700">Subtotal</h3>
                    <p class="text-2xl font-bold text-blue-600">Rp{{ number_format($totalActualSales, 2, ',', '.') }}</p>
                </div>
        
                <!-- Total Produk Terjual -->
                <div class="p-4 bg-white shadow rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-700">Produk Terjual</h3>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($totalProductsSold, 0, ',', '.') }}</p>
                </div>

                @role('admin|owner')
                <!-- Total Dilihat (Page Views) -->
                <div class="p-4 bg-white shadow rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-700">Total Dilihat</h3>
                    <p class="text-2xl font-bold text-teal-600">{{ number_format($totalPageViews ?? 0, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">Total kunjungan halaman</p>
                </div>

                <!-- Pengunjung Unik (Unique IP) -->
                <div class="p-4 bg-white shadow rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-700">Pengunjung Unik</h3>
                    <p class="text-2xl font-bold text-indigo-600">{{ number_format($totalUniqueVisitors ?? 0, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">Berdasarkan IP unik</p>
                </div>
                @endrole
            </div>
        </div>
    
        @livewire('dashboard.monthly-turnover-chart')
        

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6 mb-2">

            @livewire('dashboard.product-sales-chart')
            @livewire('dashboard.stock-warning')

        </div>

        
    </div>
    @endcan
</div>
