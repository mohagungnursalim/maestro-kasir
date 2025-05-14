<div>
    <div class="bg-white shadow-md rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Stok Rendah</h3>

        @if (!$productExists)
            <p class="text-gray-500 text-center py-4">Belum ada produk yang ditambahkan 😶‍🌫️</p>
        @elseif (count($lowStockProducts) === 0)
            <p class="text-gray-500 text-center py-4">Semua stok aman 🎉</p>
        @else
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2 text-left">Produk</th>
                        <th class="px-4 py-2 text-center">Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lowStockProducts as $product)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $product->name }}</td>
                            <td class="px-4 py-2 text-center text-red-500 font-semibold">{{ $product->stock }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
