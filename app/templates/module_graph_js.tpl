    <script type="text/javascript">
        google.charts.load('current', { packages: [ 'bar' ] });
        google.charts.load("current", { packages: [ 'corechart' ] });
        google.charts.setOnLoadCallback(drawHourlyChart);
        google.charts.setOnLoadCallback(drawDailyChart);
        google.charts.setOnLoadCallback(drawMonthlyChart);

        function formatBytes(bytes, delimiter = 'KB', decimals = 2) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

            const i = sizes.indexOf(delimiter);

            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
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

            data.sort({
                column: 0
            });

            let options = {
                title: 'Hourly Network Traffic',
                subtitle: 'over last 24 hours',
                vAxis: {
                    format: '##.## {$hourlyLargestPrefix}',
                    gridlines: { count: 10 },
                    scaleType: 'log'
                },
                hAxis: { format: 'd/M/yy HH:mm' }
            };

            let chart = new google.charts.Bar(document.getElementById('hourlyNetworkTrafficGraph'));
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
                subtitle: 'over last 29 days (most recent first)',
                vAxis: { format: '##.## {$dailyLargestPrefix}' },
                hAxis: { format: 'dd/MM/YYYY' }
            };

            data.sort({
                column: 0,
                desc: true
            });

            let chart = new google.charts.Bar(document.getElementById('dailyNetworkTrafficGraph'));
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

            data.sort({
                column: 0,
                desc: true
            });

            let options = {
                title: 'Monthly Network Traffic',
                subtitle: 'over last 12 months',
                strictFirstColumnType: false,
                vAxis: { format: '##.## {$monthlyLargestPrefix}' },
                hAxis: { format: 'MMMM YYYY' },
            };

            let chart = new google.charts.Bar(document.getElementById('monthlyNetworkTrafficGraph'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }
    </script>
