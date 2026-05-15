<div class="bg-white shadow rounded-lg p-4 mb-3">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h3 class="text-lg font-semibold text-gray-700"><i class="fas fa-clock text-indigo-500 mr-2"></i>Jam Tersibuk</h3>
    </div>

    <div class="relative h-56 w-full" wire:ignore>
        <div id="peakHourChart" style="width: 100%; height: 100%;">
            <div class="flex flex-col items-center justify-center w-full h-full text-gray-400">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 text-indigo-400"></i>
                <span class="text-sm animate-pulse">Memuat grafik...</span>
            </div>
        </div>
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
        function doRenderPeakHourChart(raw) {
            if (typeof google === 'undefined' || typeof google.visualization === 'undefined' || typeof google.visualization.DataTable === 'undefined') {
                setTimeout(() => doRenderPeakHourChart(raw), 100);
                return;
            }
            
            const data = new google.visualization.DataTable();
            data.addColumn('string', 'Jam');
            data.addColumn('number', 'Jumlah Order');
            data.addColumn({type: 'string', role: 'style'});
            data.addColumn({type: 'string', role: 'tooltip'});

            const orders = raw.map(r => r.total_orders);
            const maxOrders = Math.max(...orders, 1);

            for (let i = 0; i < raw.length; i++) {
                let r = raw[i];
                let isPeak = (r.total_orders === maxOrders && r.total_orders > 0);
                let style = isPeak ? 'color: #f97316' : 'color: #6366f1';
                let revFormatted = 'Rp ' + r.total_revenue.toLocaleString('id-ID');
                let tooltip = 'Jam: ' + r.hour + '\nOrder: ' + r.total_orders + '\nRevenue: ' + revFormatted;
                
                data.addRow([r.hour, r.total_orders, style, tooltip]);
            }

            const options = {
                legend: { position: 'none' },
                chartArea: {width: '90%', height: '70%'},
                vAxis: { minValue: 0, format: 'short' },
                bar: { groupWidth: '80%' }
            };

            const chart = new google.visualization.ColumnChart(document.getElementById('peakHourChart'));
            chart.draw(data, options);
        }

        window.renderPeakHourChart = function() {
            setTimeout(() => {
                const initialPeakData = @json($hourlyData);
                doRenderPeakHourChart(initialPeakData);
            }, 300);
        }

        window.addEventListener('update-peak-chart', (e) => {
             const ev = e.detail[0] || e.detail;
             setTimeout(() => {
                 doRenderPeakHourChart(ev.hourlyData);
             }, 600);
        });

        window.renderPeakHourChart();
    </script>
</div>
