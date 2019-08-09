    <script type="text/javascript">
        google.charts.load('current', { 'packages': [ 'bar' ] });
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
            let data = google.visualization.arrayToDataTable([
                [{ type: 'datetime', label: 'Hour' }, 'Traffic In', 'Traffic Out', 'Total Traffic'],
{foreach from=$hourlyGraphData key=key item=value}
                [new {$value.label}, formatBytes({$value.rx}, '{$hourlyLargestPrefix}'), formatBytes({$value.tx}, '{$hourlyLargestPrefix}'), formatBytes({$value.total}, '{$hourlyLargestPrefix}')],
{/foreach}
            ]);

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
            let data = google.visualization.arrayToDataTable([
                ['Day', 'Traffic In', 'Traffic Out', 'Total Traffic'],
{foreach from=$dailyGraphData key=key item=value}
                [new {$value.label}, formatBytes({$value.rx}, '{$dailyLargestPrefix}'), formatBytes({$value.tx}, '{$dailyLargestPrefix}'), formatBytes({$value.total}, '{$dailyLargestPrefix}')],
{/foreach}
            ]);

            let options = {
                title: 'Daily Network Traffic',
                subtitle: 'over last 29 days (most recent first)',
                vAxis: { format: '##.## {$dailyLargestPrefix}' },
                hAxis: { format: 'd/M/yy' }
            };

            let chart = new google.charts.Bar(document.getElementById('dailyNetworkTrafficGraph'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }

        function drawMonthlyChart()
        {
            let data = google.visualization.arrayToDataTable([
                ['Month', 'Traffic In', 'Traffic Out', 'Total Traffic'],
{foreach from=$monthlyGraphData key=key item=value}
                ['{$value.label}', formatBytes({$value.rx}, '{$monthlyLargestPrefix}'), formatBytes({$value.tx}, '{$monthlyLargestPrefix}'), formatBytes({$value.total}, '{$monthlyLargestPrefix}')],
{/foreach}
            ]);

            let options = {
                title: 'Monthly Network Traffic',
                subtitle: 'over last 12 months',
                vAxis: { format: '##.## {$monthlyLargestPrefix}' }
            };

            let chart = new google.charts.Bar(document.getElementById('monthlyNetworkTrafficGraph'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }
    </script>
