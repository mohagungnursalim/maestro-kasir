<div class="bg-white shadow-md rounded-lg p-4">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Produk Terlaris</h3>

    <!-- Tab Filter (Select List) -->
    <div class="mb-4">
        <select id="chartFilter" class="px-4 py-2 border rounded bg-white text-gray-700">
            <option value="daily">Hari Ini</option>
            <option value="weekly">Minggu Ini</option>
            <option value="monthly">Bulan Ini</option>
            <option value="yearly">Tahun Ini</option>
        </select>
    </div>

    <!-- Chart Canvas -->
    <div class="mx-auto">
        <canvas id="salesChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.getElementById("chartFilter").addEventListener("change", function () {
            updateChart(this.value);
        });

    </script>

    <script>
        function initChart() {
            setTimeout(() => {
                const ctx = document.getElementById("salesChart") ? .getContext("2d");
                if (!ctx) return; // Cegah error jika elemen tidak ditemukan

                // Hapus chart lama jika ada
                if (window.salesChart instanceof Chart) {
                    window.salesChart.destroy();
                }

                let salesData = {
                    daily: @json($dailySales),
                    weekly: @json($weeklySales),
                    monthly: @json($monthlySales),
                    yearly: @json($yearlySales),
                };

                function getChartData(type) {
                    return {
                        labels: salesData[type].map(item => item.name),
                        datasets: [{
                            data: salesData[type].map(item => item.total),
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
                    data: getChartData('daily'),
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            } // Ini menghilangkan legend
                        }
                    }
                });

                window.updateChart = function (type) {
                    window.salesChart.data = getChartData(type);
                    window.salesChart.update();
                };

            }, 300); // Tunggu 300ms agar Livewire selesai render
        }

        // Inisialisasi ulang saat navigasi dengan Livewire selesai
        document.addEventListener("livewire:navigated", initChart);

    </script>


</div>
