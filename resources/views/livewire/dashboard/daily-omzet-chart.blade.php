<div
    class="bg-white shadow rounded-lg p-4 mb-3"
    x-data
    x-on:global-filter-updated.window="$wire.loadDailyOmzet($event.detail[0]?.filter || $event.detail.filter)"
>
    {{-- Header + Summary --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-700">Omzet & Pengeluaran</h3>
            <p class="text-xs text-gray-400 mt-0.5">Pendapatan dan Pengeluaran nyata</p>
        </div>

        {{-- summary chips --}}
        <div class="flex flex-wrap gap-2 text-xs">
            <div class="flex items-center gap-1.5 bg-blue-50 border border-blue-100 rounded-lg px-3 py-1.5">
                <i class="fas fa-calendar-check text-blue-400"></i>
                <div>
                    <span class="text-gray-400 block leading-none">Hari Aktif</span>
                    <span class="font-bold text-blue-700">{{ $activeDays }} hari</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-1.5">
                <i class="fas fa-chart-line text-emerald-400"></i>
                <div>
                    <span class="text-gray-400 block leading-none">Rata-rata / Hari</span>
                    <span class="font-bold text-emerald-700">Rp{{ number_format($avgOmzet, 0, ',', '.') }}</span>
                </div>
            </div>
            @if ($topDayLabel)
            <div class="flex items-center gap-1.5 bg-amber-50 border border-amber-100 rounded-lg px-3 py-1.5">
                <i class="fas fa-trophy text-amber-400"></i>
                <div>
                    <span class="text-gray-400 block leading-none">Hari Terbaik</span>
                    <span class="font-bold text-amber-700">
                        {{ \Carbon\Carbon::parse($topDayLabel)->translatedFormat('d M') }}
                        · Rp{{ number_format($topDayOmzet, 0, ',', '.') }}
                    </span>
                </div>
            </div>
            @endif
            <div class="flex items-center gap-1.5 bg-red-50 border border-red-100 rounded-lg px-3 py-1.5">
                <i class="fas fa-money-bill-wave text-red-400"></i>
                <div>
                    <span class="text-gray-400 block leading-none">Total Pengeluaran</span>
                    <span class="font-bold text-red-700">Rp{{ number_format($totalExpense, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-1.5">
                <i class="fas fa-sack-dollar text-emerald-500"></i>
                <div>
                    <span class="text-gray-400 block leading-none">Total Keuntungan</span>
                    <span class="font-bold text-emerald-700">Rp{{ number_format($totalProfit, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="relative h-52 w-full" wire:ignore>
        <div id="dailyOmzetChart" style="width: 100%; height: 100%;">
            <div class="flex flex-col items-center justify-center w-full h-full text-gray-400">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 text-indigo-400"></i>
                <span class="text-sm animate-pulse">Memuat grafik...</span>
            </div>
        </div>
    </div>

    <script>
        function initDailyOmzetChart(labels, dataOmzet, dataExpense, dataProfit) {
            if (typeof google === 'undefined' || typeof google.visualization === 'undefined' || typeof google.visualization.DataTable === 'undefined') {
                setTimeout(() => initDailyOmzetChart(labels, dataOmzet, dataExpense, dataProfit), 100);
                return;
            }

            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Waktu');
            data.addColumn('number', 'Omset');
            data.addColumn({type: 'string', role: 'style'}); // for Omset color
            data.addColumn('number', 'Pengeluaran');
            data.addColumn('number', 'Keuntungan');

            const shortLabels = labels.map(l => {
                if (l.match(/^\d{4}-\d{2}-\d{2}$/)) return l.split('-')[2];
                if (l.match(/^\d{4}-\d{2}$/)) {
                    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                    return months[parseInt(l.split('-')[1]) - 1];
                }
                return l; 
            });

            const maxVal = Math.max(...dataOmzet, 1);
            for (var i = 0; i < labels.length; i++) {
                var valOmzet = parseFloat(dataOmzet[i]) || 0;
                var style = (valOmzet === maxVal && valOmzet > 0) ? 'color: #eab308' : 'color: #6366f1';
                
                data.addRow([
                    shortLabels[i], 
                    valOmzet,
                    style,
                    parseFloat(dataExpense[i]) || 0,
                    parseFloat(dataProfit[i]) || 0
                ]);
            }

            var options = {
                seriesType: 'bars',
                series: {
                    0: { color: '#6366f1' }, // Default Omset bar color (overridden by style role)
                    1: { color: '#ef4444' }, // Expense bar
                    2: { type: 'line', color: '#10b981', lineWidth: 2, pointSize: 4 }  // Profit line
                },
                chartArea: {width: '85%', height: '70%'},
                legend: { position: 'top' },
                vAxis: { minValue: 0, format: 'short' }
            };

            var chart = new google.visualization.ComboChart(document.getElementById('dailyOmzetChart'));
            chart.draw(data, options);
        }

        window.renderDailyOmzetFromWire = function() {
            setTimeout(() => {
                const labels       = Object.values(@json(array_keys($dailyOmzet)) || {});
                const dataOmzet    = Object.values(@json(array_values($dailyOmzet)) || {});
                const dataExpense  = Object.values(@json(array_values($dailyExpense)) || {});
                const dataProfit   = Object.values(@json(array_values($dailyProfit)) || {});
                initDailyOmzetChart(labels, dataOmzet, dataExpense, dataProfit);
            }, 300);
        }


        
        window.addEventListener('update-daily-omzet-chart', function (e) {
            const ev = e.detail[0] || e.detail;
            setTimeout(() => {
                if (document.getElementById('dailyOmzetChart')) {
                    initDailyOmzetChart(ev.labels, ev.dataOmzet, ev.dataExpense, ev.dataProfit);
                }
            }, 600);
        });

        window.renderDailyOmzetFromWire();
    </script>
</div>
