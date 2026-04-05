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
            {{-- Donut chart --}}
            <div class="relative w-40 h-40 flex-shrink-0" wire:ignore>
                <canvas id="paymentSplitChart" width="160" height="160"></canvas>
            </div>

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

    <script>
        (function () {
            function renderPaymentSplitChart() {
                setTimeout(() => {
                    const ctx = document.getElementById('paymentSplitChart')?.getContext('2d');
                    if (!ctx) return;

                    if (window.paymentSplitChart instanceof Chart) {
                        window.paymentSplitChart.destroy();
                    }

                    const qris  = {{ $qrisTotal }};
                    const tunai = {{ $tunaiTotal }};
                    const other = {{ $otherTotal }};

                    if (qris + tunai + other === 0) return;

                    window.paymentSplitChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['QRIS', 'Tunai', 'Lainnya'],
                            datasets: [{
                                data: [qris, tunai, other],
                                backgroundColor: [
                                    'rgba(99, 102, 241, 0.85)',
                                    'rgba(16, 185, 129, 0.85)',
                                    'rgba(251, 191, 36, 0.85)',
                                ],
                                borderColor: ['#6366f1', '#10b981', '#fbbf24'],
                                borderWidth: 2,
                                hoverOffset: 6,
                            }]
                        },
                        options: {
                            responsive: false,
                            cutout: '70%',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => {
                                            const val = ctx.raw;
                                            return ' Rp ' + val.toLocaleString('id-ID');
                                        }
                                    }
                                }
                            }
                        }
                    });

                    window.addEventListener('update-payment-split-chart', (e) => {
                        const { qris, tunai, other } = e.detail;
                        if(window.paymentSplitChart) {
                            window.paymentSplitChart.data.datasets[0].data = [qris, tunai, other];
                            window.paymentSplitChart.update();
                        }
                    });
                }, 200);
            }

            document.addEventListener('DOMContentLoaded', renderPaymentSplitChart);
            document.addEventListener('livewire:navigated', renderPaymentSplitChart);
        })();
    </script>
</div>
