<div class="bg-white shadow rounded-lg p-4 mb-3">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Pengeluaran Bulanan</h3>
    <div class="relative h-64">
        <canvas id="monthlyExpenseChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        window.initMonthlyExpenseChart = function() {
            setTimeout(() => {
                if (typeof Chart === 'undefined') {
                    setTimeout(window.initMonthlyExpenseChart, 100);
                    return;
                }

                const ctx = document.getElementById('monthlyExpenseChart')?.getContext('2d');
                if (!ctx) return;

                if (window.monthlyExpenseLineChart instanceof Chart) {
                    window.monthlyExpenseLineChart.destroy();
                }

                const data = @json($monthlyExpense);

                window.monthlyExpenseLineChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: [
                            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
                        ],
                        datasets: [{
                            label: 'Pengeluaran',
                            data: data,
                            borderColor: 'rgba(239, 68, 68, 1)',
                            backgroundColor: 'rgba(239, 68, 68, 0.2)',
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
                                    label: ctx => 'Rp ' + ctx.raw.toLocaleString('id-ID', {
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
        window.initMonthlyExpenseChart();
    </script>
</div>
