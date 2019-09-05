    <script type="text/javascript">
        google.charts.load('current', { packages: [ 'bar' ] });
        google.charts.load("current", { packages: [ 'corechart' ] });
        
        google.charts.setOnLoadCallback(drawHourlyChart);
        google.charts.setOnLoadCallback(drawDailyChart);
        google.charts.setOnLoadCallback(drawMonthlyChart);

        function drawHourlyChart()
        {
            var data = new google.visualization.DataTable();

            data.addColumn('date', 'Hour');
            data.addColumn('number', 'Traffic In');
            data.addColumn('number', 'Traffic Out');
            data.addColumn('number', 'Total Traffic');

            data.addRows([
{foreach from=$hourlyGraphData key=key item=value}
                [new {$value.label}, {$value.rx}, {$value.tx}, {$value.total}],
{/foreach}
            ]);

            let options = {
                title: 'Hourly Network Traffic',
                orientation: 'horizontal',
                legend: { position: 'right' },
                explorer: { 
                    axis: 'horizontal',
                    maxZoomIn: 4.0,
                    maxZoomOut: 3.0
            	},
                vAxis: {
                    title: 'Data',
                    format: '##.## {$hourlyLargestPrefix}'
                },
                hAxis: {
                    title: 'Hour',
                    format: 'HH:mm',
                    direction: -1,
                    ticks: [
{foreach from=$hourlyGraphData key=key item=value}
                        new {$value.label},
{/foreach}
                    ]
                }
            };
            
            var formatDate = new google.visualization.DateFormat({ pattern: 'dd/MM/yyyy HH:mm' });
            formatDate.format(data, 0);
            
            var formatNumber = new google.visualization.NumberFormat({ pattern: '##.## {$hourlyLargestPrefix}' });
            formatNumber.format(data, 1);
            formatNumber.format(data, 2);
            formatNumber.format(data, 3);

            let chart = new google.visualization.BarChart(document.getElementById('hourlyNetworkTrafficGraph'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }

        function drawDailyChart()
        {
            var data = new google.visualization.DataTable();

            data.addColumn('date', 'Day');
            data.addColumn('number', 'Traffic In');
            data.addColumn('number', 'Traffic Out');
            data.addColumn('number', 'Total Traffic');

            data.addRows([
{foreach from=$dailyGraphData key=key item=value}
                [new {$value.label}, {$value.rx}, {$value.tx}, {$value.total}],
{/foreach}
            ]);
            
            let options = {
                title: 'Daily Network Traffic',
                orientation: 'horizontal',
                legend: { position: 'right' },
                explorer: { 
                    axis: 'horizontal',
                    maxZoomIn: 4.0,
                    maxZoomOut: 3.0
            	},
                vAxis: {
                    title: 'Data',
                    format: '##.## {$dailyLargestPrefix}'
                },
                hAxis: {
                    title: 'Day',
                    format: 'dd/MM/YYYY',
                    direction: -1
                }
            };
            
            var formatDate = new google.visualization.DateFormat({ pattern: 'dd/MM/yyyy' });
            formatDate.format(data, 0);
            
            var formatNumber = new google.visualization.NumberFormat({ pattern: '##.## {$dailyLargestPrefix}' });
            formatNumber.format(data, 1);
            formatNumber.format(data, 2);
            formatNumber.format(data, 3);

            let chart = new google.visualization.BarChart(document.getElementById('dailyNetworkTrafficGraph'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }

        function drawMonthlyChart()
        {
            var data = new google.visualization.DataTable();

            data.addColumn('date', 'Month');
            data.addColumn('number', 'Traffic In');
            data.addColumn('number', 'Traffic Out');
            data.addColumn('number', 'Total Traffic');

            data.addRows([
{foreach from=$monthlyGraphData key=key item=value}
                [new {$value.label}, {$value.rx}, {$value.tx}, {$value.total}],
{/foreach}
            ]);

            let options = {
                title: 'Monthly Network Traffic',
                orientation: 'horizontal',
                legend: { position: 'right' },
                explorer: { 
                    axis: 'horizontal',
                    maxZoomIn: 4.0,
                    maxZoomOut: 3.0
            	},
                vAxis: {
                    title: 'Data',
                    format: '##.## {$monthlyLargestPrefix}'
                },
                hAxis: {
                    title: 'Month',
                    format: 'MMMM YYYY',
                    direction: -1
                }
            };
            
            var formatDate = new google.visualization.DateFormat({ pattern: 'MMMM YYYY' });
            formatDate.format(data, 0);
            
            var formatNumber = new google.visualization.NumberFormat({ pattern: '##.## {$monthlyLargestPrefix}' });
            formatNumber.format(data, 1);
            formatNumber.format(data, 2);
            formatNumber.format(data, 3);

            let chart = new google.visualization.BarChart(document.getElementById('monthlyNetworkTrafficGraph'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }
    </script>
