<div class="bg-white shadow rounded-lg p-4 mb-3">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Omzet & Pengeluaran Bulanan</h3>
    <div class="relative h-64">
        <canvas id="monthlyChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        window.initMonthlyTurnoverChart = function() {
            setTimeout(() => {
                if (typeof Chart === 'undefined') {
                    setTimeout(window.initMonthlyTurnoverChart, 100);
                    return;
                }

                const ctx = document.getElementById('monthlyChart')?.getContext('2d');
                if (!ctx) return;

                if (window.monthlyChart instanceof Chart) {
                    window.monthlyChart.destroy();
                }

                const dataTurnover = @json($monthlyTurnover);
                const dataExpense  = @json($monthlyExpense);

                window.monthlyChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: [
                            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
                        ],
                        datasets: [
                            {
                                label: 'Omset',
                                data: dataTurnover,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                tension: 0.4,
                                fill: true,
                            },
                            {
                                label: 'Pengeluaran',
                                data: dataExpense,
                                borderColor: 'rgba(239, 68, 68, 1)',
                                backgroundColor: 'rgba(239, 68, 68, 0.2)',
                                tension: 0.4,
                                fill: true,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: value => 'Rp ' + value.toLocaleString('id-ID', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: ctx => ctx.dataset.label + ': Rp ' + ctx.raw.toLocaleString('id-ID', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })
                                }
                            },
                            legend: {
                                display: true
                            }
                        }
                    }
                });
            }, 300);
        }
        // Jalankan segera saat script di-evaluasi oleh Livewire (saat navigasi tanpa refresh)
        window.initMonthlyTurnoverChart();
    </script>
</div>
