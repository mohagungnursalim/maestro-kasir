<div class="bg-white shadow rounded-lg p-4 mb-3">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Omset Bulanan</h3>
    <div class="relative h-64">
        <canvas id="monthlyChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        function initMonthlyTurnoverChart() {
            setTimeout(() => {
                const ctx = document.getElementById('monthlyChart')?.getContext('2d');
                if (!ctx) return;

                if (window.monthlyChart instanceof Chart) {
                    window.monthlyChart.destroy();
                }

                const data = @json($monthlyTurnover);

                window.monthlyChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: [
                            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
                        ],
                        datasets: [{
                            label: 'Omset',
                            data: data,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.4,
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: value => 'Rp ' + value.toLocaleString('id-ID')
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: ctx => 'Rp ' + ctx.raw.toLocaleString('id-ID')
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

        document.addEventListener('DOMContentLoaded', initMonthlyTurnoverChart);
        document.addEventListener('livewire:navigated', initMonthlyTurnoverChart);
    </script>
</div>
