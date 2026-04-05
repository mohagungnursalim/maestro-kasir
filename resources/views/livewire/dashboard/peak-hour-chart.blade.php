<div class="bg-white shadow rounded-lg p-4 mb-3">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h3 class="text-lg font-semibold text-gray-700"><i class="fas fa-clock text-indigo-500 mr-2"></i>Jam Tersibuk</h3>
    </div>

    <div class="relative h-56" wire:ignore>
        <canvas id="peakHourChart"></canvas>
    </div>

    {{-- Peak hour summary badge --}}
    @php
        $peakHour = collect($hourlyData)->sortByDesc('total_orders')->first();
    @endphp
    @if ($peakHour && $peakHour['total_orders'] > 0)
        <div class="mt-3 flex items-center gap-2 text-sm text-gray-500">
            <span class="inline-flex items-center gap-1 bg-orange-100 text-orange-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                <i class="fas fa-fire text-orange-500 mr-2"></i> Puncak: {{ $peakHour['hour'] }} — {{ $peakHour['total_orders'] }} order
            </span>
            <span class="text-gray-400 text-xs">Rp{{ number_format($peakHour['total_revenue'], 0, ',', '.') }}</span>
        </div>
    @endif

    <script>
        (function () {
            function renderPeakHourChart() {
                setTimeout(() => {
                    const ctx = document.getElementById('peakHourChart')?.getContext('2d');
                    if (!ctx) return;

                    if (window.peakHourChart instanceof Chart) {
                        window.peakHourChart.destroy();
                    }

                    const raw = @json($hourlyData);
                    const labels  = raw.map(r => r.hour);
                    const orders  = raw.map(r => r.total_orders);
                    const revenue = raw.map(r => r.total_revenue);

                    const maxOrders = Math.max(...orders);
                    const bgColors = orders.map(v =>
                        v === maxOrders && v > 0
                            ? 'rgba(249, 115, 22, 0.85)'   // orange — peak
                            : 'rgba(99, 102, 241, 0.55)'   // indigo — normal
                    );

                    window.peakHourChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Jumlah Order',
                                data: orders,
                                backgroundColor: bgColors,
                                borderRadius: 4,
                                borderSkipped: false,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        afterLabel: function(ctx) {
                                            const rev = revenue[ctx.dataIndex];
                                            if (rev > 0) {
                                                return 'Revenue: Rp ' + rev.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { size: 10 } }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        callback: v => Number.isInteger(v) ? v : ''
                                    }
                                }
                            }
                        }
                    });

                    window.addEventListener('update-peak-chart', (e) => {
                        const newRaw = e.detail.hourlyData;
                        const newLabels  = newRaw.map(r => r.hour);
                        const newOrders  = newRaw.map(r => r.total_orders);
                        const newRevenue = newRaw.map(r => r.total_revenue);

                        const maxOrders = Math.max(...newOrders);
                        const newBgColors = newOrders.map(v =>
                            v === maxOrders && v > 0
                                ? 'rgba(249, 115, 22, 0.85)'   // orange — peak
                                : 'rgba(99, 102, 241, 0.55)'   // indigo — normal
                        );

                        if(window.peakHourChart) {
                            window.peakHourChart.data.labels = newLabels;
                            window.peakHourChart.data.datasets[0].data = newOrders;
                            window.peakHourChart.data.datasets[0].backgroundColor = newBgColors;
                            
                            // Hacky way to inject revenue array back to tooltip via mapping
                            window.peakHourChart.options.plugins.tooltip.callbacks.afterLabel = function(ctx) {
                                const rev = newRevenue[ctx.dataIndex];
                                if (rev > 0) {
                                    return 'Revenue: Rp ' + rev.toLocaleString('id-ID');
                                }
                            };
                            window.peakHourChart.update();
                        }
                    });
                }, 200);
            }

            document.addEventListener('DOMContentLoaded', renderPeakHourChart);
            document.addEventListener('livewire:navigated', renderPeakHourChart);
        })();
    </script>
</div>
