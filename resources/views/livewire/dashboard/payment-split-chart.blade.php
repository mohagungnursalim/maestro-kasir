<div class="bg-white shadow-md rounded-lg p-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h3 class="text-lg font-semibold text-gray-700"><i class="fas fa-credit-card text-blue-500 mr-2"></i>Metode Pembayaran</h3>
    </div>

    @php
        $grandTotal = $qrisTotal + $tunaiTotal + $otherTotal;
    @endphp

    @if ($grandTotal == 0)
        <div class="flex items-center justify-center h-48 text-gray-400 text-sm">
            Belum ada transaksi pada periode ini.
        </div>
    @else
        <div class="flex flex-col sm:flex-row items-center gap-6">


            {{-- Legend & breakdown --}}
            <div class="flex-1 w-full space-y-3">
                {{-- QRIS --}}
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block w-3 h-3 rounded-full bg-indigo-500"></span>
                            <span class="font-medium text-gray-700">QRIS</span>
                        </span>
                        <span class="text-gray-500">{{ $qrisPct }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full transition-all duration-500" style="width: {{ $qrisPct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5 text-right">Rp{{ number_format($qrisTotal, 0, ',', '.') }}</p>
                </div>

                {{-- Tunai --}}
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="font-medium text-gray-700">Tunai</span>
                        </span>
                        <span class="text-gray-500">{{ $tunaiPct }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $tunaiPct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5 text-right">Rp{{ number_format($tunaiTotal, 0, ',', '.') }}</p>
                </div>

                {{-- Lainnya --}}
                @if ($otherTotal > 0)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block w-3 h-3 rounded-full bg-amber-400"></span>
                            <span class="font-medium text-gray-700">Lainnya</span>
                        </span>
                        <span class="text-gray-500">{{ $otherPct }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-amber-400 h-2 rounded-full transition-all duration-500" style="width: {{ $otherPct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5 text-right">Rp{{ number_format($otherTotal, 0, ',', '.') }}</p>
                </div>
                @endif
            </div>
        </div>
    @endif

</div>
