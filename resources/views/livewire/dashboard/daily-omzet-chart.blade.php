<div
    class="bg-white shadow rounded-lg p-4 mb-3"
    x-data
    x-on:global-filter-updated.window="$wire.loadDailyOmzet($event.detail.filter)"
>
    {{-- Header + Summary --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-700">Omzet Harian</h3>
            <p class="text-xs text-gray-400 mt-0.5">Omzet nyata per hari dalam periode aktif</p>
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
        </div>
    </div>

    {{-- Chart --}}
    <div class="relative h-52">
        <canvas id="dailyOmzetChart"></canvas>
    </div>

    <script>
        function initDailyOmzetChart(labels, data) {
            const ctx = document.getElementById('dailyOmzetChart')?.getContext('2d');
            if (!ctx) return;

            if (window._dailyOmzetChart instanceof Chart) {
                window._dailyOmzetChart.destroy();
            }

            // Format label agar lebih ringkas (ambil bagian tanggal saja)
            const shortLabels = labels.map(l => {
                if (l.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    // YYYY-MM-DD → "dd"
                    return l.split('-')[2];
                }
                if (l.match(/^\d{4}-\d{2}$/)) {
                    // YYYY-MM → Bulan singkat
                    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                    return months[parseInt(l.split('-')[1]) - 1];
                }
                return l; // jam (HH:00) langsung
            });

            const maxVal  = Math.max(...data, 1);
            const colors  = data.map(v => v === maxVal && v > 0 ? 'rgba(234,179,8,0.85)' : 'rgba(99,102,241,0.65)');
            const borders = data.map(v => v === maxVal && v > 0 ? 'rgba(161,98,7,1)'     : 'rgba(79,70,229,0.9)');

            window._dailyOmzetChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: shortLabels,
                    datasets: [{
                        label: 'Omzet',
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
                                    // Tampilkan label asli (bukan shortLabel)
                                    return labels[items[0].dataIndex];
                                },
                                label: ctx => 'Rp ' + ctx.raw.toLocaleString('id-ID')
                            }
                        }
                    }
                }
            });
        }

        function renderDailyOmzetFromWire() {
            const labels = @json(array_keys($dailyOmzet));
            const data   = @json(array_values($dailyOmzet));
            initDailyOmzetChart(labels, data);
        }

        document.addEventListener('DOMContentLoaded', renderDailyOmzetFromWire);
        document.addEventListener('livewire:navigated', renderDailyOmzetFromWire);

        // Re-render setelah Livewire update komponen ini
        document.addEventListener('livewire:updated', function (e) {
            if (document.getElementById('dailyOmzetChart')) {
                renderDailyOmzetFromWire();
            }
        });
    </script>
</div>
