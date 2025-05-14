<div>
    <div class="bg-white shadow rounded-lg p-4 mb-3">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Omset Bulanan</h3>
        <div class="relative h-64"> 
            <canvas id="turnoverChart"></canvas>
        </div>
    </div>


<script>
    let turnoverChartInstance = null;

    function initTurnoverChart() {
        const canvas = document.getElementById('turnoverChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        // Hapus chart sebelumnya jika ada
        if (turnoverChartInstance !== null) {
            turnoverChartInstance.destroy();
        }

        turnoverChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                    'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
                ],
                datasets: [{
                    label: 'Omset',
                    data: @json($monthlyTurnover),
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
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initTurnoverChart);
    document.addEventListener('livewire:navigated', () => {
        // Delay agar canvas benar-benar tersedia setelah re-render
        setTimeout(() => {
            initTurnoverChart();
        }, 100);
    });
</script>
</div>
