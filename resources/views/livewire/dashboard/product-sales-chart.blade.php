<div class="bg-white shadow-md rounded-lg p-4">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Produk Terlaris</h3>

    <!-- Chart Canvas -->
    <div class="mx-auto w-full h-56" wire:ignore>
        <div id="salesChart" style="width: 100%; height: 100%;">
            <div class="flex flex-col items-center justify-center w-full h-full text-gray-400">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 text-indigo-400"></i>
                <span class="text-sm animate-pulse">Memuat grafik...</span>
            </div>
        </div>
    </div>

    <script>
        function doRenderProductSalesChart(salesData) {
            if (typeof google === 'undefined' || typeof google.visualization === 'undefined') {
                setTimeout(() => doRenderProductSalesChart(salesData), 100);
                return;
            }

            const data = new google.visualization.DataTable();
            data.addColumn('string', 'Produk');
            data.addColumn('number', 'Porsi Terjual');
            data.addColumn({type: 'string', role: 'style'});

            const colors = ['#36A2EB', '#4BC0C0', '#FFCE56', '#9966FF', '#FF9F40'];
            
            for (let i = 0; i < (salesData || []).length; i++) {
                let item = salesData[i];
                let color = colors[i % colors.length];
                data.addRow([item.name, item.total, 'color: ' + color]);
            }

            const options = {
                legend: { position: 'none' },
                chartArea: {width: '80%', height: '70%'},
                vAxis: { minValue: 0, format: 'short' },
                bar: { groupWidth: '60%' }
            };

            const chart = new google.visualization.ColumnChart(document.getElementById('salesChart'));
            chart.draw(data, options);
        }

        window.initProductSalesChart = function() {
            setTimeout(() => {
                const initialSalesData = @json($salesData);
                doRenderProductSalesChart(initialSalesData);
            }, 300);
        }

        window.addEventListener('productSalesDataUpdated', (e) => {
            const freshData = (e.detail[0] || e.detail).salesData;
            setTimeout(() => {
                doRenderProductSalesChart(freshData);
            }, 600);
        });

        window.initProductSalesChart();
    </script>
</div>
