    <script type="text/javascript">
        google.charts.load('current', { packages: [ 'bar' ] });
        google.charts.load("current", { packages: [ 'corechart' ] });

        google.charts.setOnLoadCallback(drawFiveChart);
        google.charts.setOnLoadCallback(drawHourlyChart);
        google.charts.setOnLoadCallback(drawDailyChart);
        google.charts.setOnLoadCallback(drawMonthlyChart);

        function drawFiveChart()
        {
            {if isset($fiveGraphData[0]['label'])}
            var data = new google.visualization.DataTable();

            data.addColumn('datetime', 'Time');
            data.addColumn('number', 'Traffic In');
            data.addColumn('number', 'Traffic Out');
            data.addColumn('number', 'Total Traffic');

            data.addRows([
{foreach from=$fiveGraphData key=key item=value}
                [new {$value.label}, {$value.rx}, {$value.tx}, {$value.total}],
{/foreach}
            ]);

            let endD = (new {$fiveGraphData[0]['label']}).getTime();

            let options = {
                title: 'Five minute Network Traffic',
                orientation: 'horizontal',
                legend: { position: 'right' },
                explorer: { 
                    axis: 'horizontal',
                    zoomDelta: 1.1,
                    maxZoomIn: 0.1,
                    maxZoomOut: 10.0
            	},
                vAxis: {
                    format: '##.## {$fiveLargestPrefix}'
                },
                hAxis: {
                    direction: -1,
                    format: 'd/H:mm',
                    minorGridlines: { count: 0 },
                    title: 'Day/Hour:Minute',
                    viewWindow: {
                        min: 'Date('+(endD-7200000).toString()+')',
                        max: 'Date('+(endD+150000).toString()+')'
                    }
                }
            };

            var formatDate = new google.visualization.DateFormat({ pattern: 'dd/MM/yyyy HH:mm' });
            formatDate.format(data, 0);

            var formatNumber = new google.visualization.NumberFormat({ pattern: '##.## {$fiveLargestPrefix}' });
            formatNumber.format(data, 1);
            formatNumber.format(data, 2);
            formatNumber.format(data, 3);

            let chart = new google.visualization.BarChart(document.getElementById('fiveNetworkTrafficGraph'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
            {/if}
        }

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

            let endD = (new {$hourlyGraphData[0]['label']}).getTime();

            let options = {
                title: 'Hourly Network Traffic',
                orientation: 'horizontal',
                legend: { position: 'right' },
                explorer: { 
                    axis: 'horizontal',
                    zoomDelta: 1.1,
                    maxZoomIn: 0.1,
                    maxZoomOut: 10.0
            	},
                vAxis: {
                    format: '##.## {$hourlyLargestPrefix}'
                },
                hAxis: {
                    title: 'Day/Hour',
                    format: 'd/H',
                    direction: -1,
                    viewWindow: {
                        min: 'Date('+(endD-86400000).toString()+')',
                        max: 'Date('+(endD+1800000).toString()+')'
                    },
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

            let endD = (new {$dailyGraphData[0]['label']}).getTime();

            let options = {
                title: 'Daily Network Traffic',
                orientation: 'horizontal',
                legend: { position: 'right' },
                explorer: { 
                    axis: 'horizontal',
                    zoomDelta: 1.1,
                    maxZoomIn: 0.1,
                    maxZoomOut: 10.0
            	},
                vAxis: {
                    format: '##.## {$dailyLargestPrefix}'
                },
                hAxis: {
                    title: 'Day',
                    format: 'dd/MM/YYYY',
                    viewWindow: {
                        min: 'Date('+(endD-2592000000).toString()+')',
                        max: 'Date('+(endD+43200000).toString()+')'
                    },
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
                    zoomDelta: 1.1,
                    maxZoomIn: 0.1,
                    maxZoomOut: 10.0
            	},
                vAxis: {
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
