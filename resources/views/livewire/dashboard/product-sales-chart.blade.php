<div class="bg-white shadow-md rounded-lg p-4">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Produk Terlaris</h3>

    <!-- Chart Canvas -->
    <div class="mx-auto" wire:ignore>
        <canvas id="salesChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        window.initProductSalesChart = function() {
            setTimeout(() => {
                if (typeof Chart === 'undefined') {
                    setTimeout(window.initProductSalesChart, 100);
                    return;
                }

                const ctx = document.getElementById("salesChart")?.getContext("2d");
                if (!ctx) return;
    
                if (window.salesChart instanceof Chart) {
                    window.salesChart.destroy();
                }
    
                let salesData = @json($salesData);
    
                function applyChartData(dataArray) {
                    return {
                        labels: (dataArray || []).map(item => item.name),
                        datasets: [{
                            data: (dataArray || []).map(item => item.total),
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(75, 192, 192, 0.6)',
                                'rgba(255, 206, 86, 0.6)',
                                'rgba(153, 102, 255, 0.6)',
                                'rgba(255, 159, 64, 0.6)'
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                            borderWidth: 1,
                        }]
                    };
                }
    
                window.salesChart = new Chart(ctx, {
                    type: 'bar',
                    data: applyChartData(salesData),
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    callback: v => Number.isInteger(v) ? v : ''
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
    
                window.addEventListener('productSalesDataUpdated', (e) => {
                    const freshData = e.detail.salesData;
                    if (window.salesChart) {
                        window.salesChart.data = applyChartData(freshData);
                        window.salesChart.update();
                    }
                });
            }, 300);
        }
        
        // Jalankan segera saat script di-evaluasi oleh Livewire
        window.initProductSalesChart();
    </script>
</div>
