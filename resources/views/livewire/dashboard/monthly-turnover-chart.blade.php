<div class="bg-white shadow rounded-lg p-4 mb-3">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Omzet & Pengeluaran Bulanan</h3>
    <div class="relative h-64 w-full" wire:ignore>
        <div id="monthlyChart" style="width: 100%; height: 100%;"></div>
    </div>

    <script>
        window.initMonthlyTurnoverChart = function() {
            setTimeout(() => {
                if (typeof google === 'undefined' || typeof google.visualization === 'undefined') {
                    setTimeout(window.initMonthlyTurnoverChart, 100);
                    return;
                }

                const dataTurnover = Object.values(@json($monthlyTurnover) || {});
                const dataExpense  = Object.values(@json($monthlyExpense) || {});
                const dataProfit   = Object.values(@json($monthlyProfit) || {});

                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Bulan');
                data.addColumn('number', 'Omset');
                data.addColumn({type: 'string', role: 'style'}); // for Omset color
                data.addColumn('number', 'Pengeluaran');
                data.addColumn('number', 'Keuntungan');

                var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                
                const maxVal = Math.max(...dataTurnover, 1);

                for (var i = 0; i < 12; i++) {
                    var valOmzet = parseFloat(dataTurnover[i]) || 0;
                    var style = (valOmzet === maxVal && valOmzet > 0) ? 'color: #eab308' : 'color: #6366f1';

                    data.addRow([
                        months[i], 
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

                var chart = new google.visualization.ComboChart(document.getElementById('monthlyChart'));
                chart.draw(data, options);
            }, 300);
        }
        
        window.addEventListener('global-filter-updated', function(e) {
            setTimeout(window.initMonthlyTurnoverChart, 600);
        });
        
        window.initMonthlyTurnoverChart();
    </script>
</div>
