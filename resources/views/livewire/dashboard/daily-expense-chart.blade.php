<div
    class="bg-white shadow rounded-lg p-4 mb-3"
    x-data
    x-on:global-filter-updated.window="$wire.loadDailyExpense($event.detail.filter)"
>
    {{-- Header + Summary --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-700">Pengeluaran Harian</h3>
            <p class="text-xs text-gray-400 mt-0.5">Pengeluaran nyata per hari dalam periode aktif</p>
        </div>

        {{-- 3 summary chips --}}
        <div class="flex flex-wrap gap-2 text-xs">
            <div class="flex items-center gap-1.5 bg-blue-50 border border-blue-100 rounded-lg px-3 py-1.5">
                <i class="fas fa-calendar-check text-blue-400"></i>
                <div>
                    <span class="text-gray-400 block leading-none">Hari Aktif</span>
                    <span class="font-bold text-blue-700">{{ $activeDays }} hari</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 bg-red-50 border border-red-100 rounded-lg px-3 py-1.5">
                <i class="fas fa-chart-line text-red-400"></i>
                <div>
                    <span class="text-gray-400 block leading-none">Rata-rata / Hari</span>
                    <span class="font-bold text-red-700">Rp{{ number_format($avgExpense, 0, ',', '.') }}</span>
                </div>
            </div>
            @if ($topDayLabel)
            <div class="flex items-center gap-1.5 bg-amber-50 border border-amber-100 rounded-lg px-3 py-1.5">
                <i class="fas fa-arrow-trend-up text-amber-400"></i>
                <div>
                    <span class="text-gray-400 block leading-none">Pengeluaran Terbesar</span>
                    <span class="font-bold text-amber-700">
                        {{ \Carbon\Carbon::parse($topDayLabel)->translatedFormat('d M') }}
                        · Rp{{ number_format($topDayExpense, 0, ',', '.') }}
                    </span>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Chart --}}
    <div class="relative h-52">
        <canvas id="dailyExpenseChart"></canvas>
    </div>

    <script>
        function initDailyExpenseChart(labels, data) {
            if (typeof Chart === 'undefined') {
                setTimeout(() => initDailyExpenseChart(labels, data), 100);
                return;
            }
            
            const ctx = document.getElementById('dailyExpenseChart')?.getContext('2d');
            if (!ctx) return;

            if (window._dailyExpenseChart instanceof Chart) {
                window._dailyExpenseChart.destroy();
            }

            const shortLabels = labels.map(l => {
                if (l.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    return l.split('-')[2];
                }
                if (l.match(/^\d{4}-\d{2}$/)) {
                    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                    return months[parseInt(l.split('-')[1]) - 1];
                }
                return l; 
            });

            const maxVal  = Math.max(...data, 1);
            const colors  = data.map(v => v === maxVal && v > 0 ? 'rgba(239, 68, 68, 0.85)' : 'rgba(248, 113, 113, 0.65)');
            const borders = data.map(v => v === maxVal && v > 0 ? 'rgba(185, 28, 28, 1)'     : 'rgba(239, 68, 68, 0.9)');

            window._dailyExpenseChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: shortLabels,
                    datasets: [{
                        label: 'Pengeluaran',
                        data: data,
                        backgroundColor: colors,
                        borderColor: borders,
                        borderWidth: 1.5,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 }, maxRotation: 0 }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: {
                                font: { size: 10 },
                                callback: v => v >= 1000000
                                    ? 'Rp' + (v / 1000000).toFixed(1) + 'jt'
                                    : v >= 1000
                                        ? 'Rp' + (v / 1000).toFixed(0) + 'rb'
                                        : 'Rp' + v
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: (items) => {
                                    return labels[items[0].dataIndex];
                                },
                                label: ctx => 'Rp ' + ctx.raw.toLocaleString('id-ID')
                            }
                        }
                    }
                }
            });
        }

        window.renderDailyExpenseFromWire = function() {
            setTimeout(() => {
                const labels = @json(array_keys($dailyExpense));
                const data   = @json(array_values($dailyExpense));
                initDailyExpenseChart(labels, data);
            }, 300);
        }

        document.addEventListener('livewire:updated', function (e) {
            if (document.getElementById('dailyExpenseChart')) {
                window.renderDailyExpenseFromWire();
            }
        });
        
        window.renderDailyExpenseFromWire();
    </script>
</div>
