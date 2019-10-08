    <script type="text/javascript">
        $width = getCookie("width");
        if (!$width || ($width != window.innerWidth)) {
            //console.log(window.innerWidth);
            createCookie("width", window.innerWidth, "1");
            document.location.reload();
        }

        google.charts.load('current', { 'packages': [ 'corechart' ]});
        google.charts.load('current', { 'packages': [ 'bar' ] });
        google.charts.setOnLoadCallback(drawFiveChart);
        google.charts.setOnLoadCallback(drawHourlyChart);
        google.charts.setOnLoadCallback(drawDailyChart);
        google.charts.setOnLoadCallback(drawMonthlyChart);

        function formatBytes(bytes, delimiter = 'KB', decimals = 2) {
            if (bytes === 0) return 0;

            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['B', 'K', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'];

            const i = sizes.indexOf(delimiter);

            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm));
        }

        function createCookie(name, value, days) {
          var expires;
          if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toGMTString();
          } else {
           expires = "";
          }
          document.cookie = escape(name) + "=" + escape(value) + expires + "; path=/";
        }

        function getCookie(name) {
            var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
            return v ? v[2] : null;
        }

        function drawFiveChart()
        {

            let data = google.visualization.arrayToDataTable([
                [{literal}{ type: 'datetime', label: 'Time' }{/literal}, 'Traffic In', 'Traffic Out', 'Total Traffic'],
                {foreach from=$fiveGraphData key=key item=value}
                  [new {$value.label}, formatBytes({$value.rx}, '{$fiveLargestPrefix}'), 
                      formatBytes({$value.tx}, '{$fiveLargestPrefix}'), formatBytes({$value.total}, '{$fiveLargestPrefix}')],
                {/foreach}
            ]);

            var options = {
                title: 'Five minute Network Traffic',
                orientation: 'horizontal',
                bar: { groupWidth: '85%' },
                explorer: { axis: 'horizontal', zoomDelta: 1.1, maxZoomIn: 0.10, maxZoomOut: 10 },
                legend: { position: 'bottom' },
                chartArea: {
                    left: 50,
                    width: '95%'
                },
                hAxis: { 
                    direction: -1, 
                    format: 'H:mm', 
                    minorGridlines: { count: 0 },
                    title: 'Hour:Minute   (Scroll to zoom, Drag to pan)',
                    viewWindow: {
                        min: new {$fiveGraphData[35]['label']},
                        max: new {$fiveGraphData[0]['label']}
                    }
                },
                vAxis: {
                    format: '###.####{$fiveLargestPrefix}',
                    textStyle: { fontSize: 12 },
                    scaleType: 'log',
                    baseline: {$fiveBase}
                }
            };

            if (data.getNumberOfRows() > 0) {
                let chart = new google.visualization.BarChart(document.getElementById('fiveNetworkTrafficGraph'));
                chart.draw(data, options);
            }
        }

        function drawHourlyChart()
        {
            let data = google.visualization.arrayToDataTable([
                [{literal}{ type: 'datetime', label: 'Hour' }{/literal}, 'Traffic In', 'Traffic Out', 'Total Traffic'],
{foreach from=$hourlyGraphData key=key item=value}
                [new {$value.label}, formatBytes({$value.rx}, '{$hourlyLargestPrefix}'), formatBytes({$value.tx}, '{$hourlyLargestPrefix}'), formatBytes({$value.total}, '{$hourlyLargestPrefix}')],
{/foreach}
            ]);

            let options = {
                title: 'Hourly Network Traffic',
                subtitle: 'over last 24 hours',
                orientation: 'horizontal',
                bar: { groupWidth: '85%' },
                explorer: { axis: 'horizontal', zoomDelta: 1.1, maxZoomIn: 0.10, maxZoomOut: 10 },
                legend: { position: 'bottom' },
                chartArea: {
                    left: 50,
                    width: '95%'
                },
                hAxis: { 
                    direction: -1, 
                    format: 'd:H', 
                    minorGridlines: { count: 0 },
                    title: 'Day:Hour (Scroll to zoom, Drag to pan)',
                    viewWindow: {
                        min: new {$hourlyGraphData[23]['label']},
                        max: new {$hourlyGraphData[0]['label']}
                    }
                },
                vAxis: {
                    format: '###.####{$hourlyLargestPrefix}',
                    textStyle: { fontSize: 12 },
                    scaleType: 'log',
                    baseline: {$hourlyBase}
                }
            };

            let chart = new google.visualization.BarChart(document.getElementById('hourlyNetworkTrafficGraph'));
            chart.draw(data, options);
        }

        function drawDailyChart()
        {
            let data = google.visualization.arrayToDataTable([
                [{literal}{type: 'datetime', label: 'Date'}{/literal}, 'Traffic In', 'Traffic Out', 'Total Traffic'],
                {foreach from=$dailyGraphData key=key item=value}
                  ['{$value.label}', formatBytes({$value.rx}, '{$dailyLargestPrefix}'), 
                     formatBytes({$value.tx}, '{$dailyLargestPrefix}'), formatBytes({$value.total}, '{$dailyLargestPrefix}')],
                {/foreach}
            ]);

            let options = {
                title: 'Daily Network Traffic',
                subtitle: 'over last 30 days (most recent first)',
                orientation: 'horizontal',
                bar: { groupWidth: '85%' },
                explorer: { axis: 'horizontal', zoomDelta: 1.1, maxZoomIn: 0.10, maxZoomOut: 10 },
                legend: { position: 'bottom' },
                chartArea: {
                    left: 50,
                    width: '95%'
                },
                hAxis: { 
                    direction: -1, 
                    format: 'M/d', 
                    title: 'Month/Day  Scroll to zoom, Drag to pan',
                    viewWindow: {
                        min: new {$dailyGraphData[29]['label']},
                        max: new {$dailyGraphData[0]['label']}
                    }
                },
                vAxis: {
                    format: '###.####{$dailyLargestPrefix}',
                    textStyle: { fontSize: 12 },
                    scaleType: 'log',
                    baseline: {$dailyBase}
                }
            };

            let chart = new google.visualization.BarChart(document.getElementById('dailyNetworkTrafficGraph'));
            chart.draw(data, options);
        }

        function drawMonthlyChart()
        {
            let data = google.visualization.arrayToDataTable([
                ['Month', 'Traffic In', 'Traffic Out', 'Total Traffic'],
                {foreach from=$monthlyGraphData key=key item=value}
                  ['{$value.label}', formatBytes({$value.rx}, '{$monthlyLargestPrefix}'), 
                    formatBytes({$value.tx}, '{$monthlyLargestPrefix}'), formatBytes({$value.total}, '{$monthlyLargestPrefix}')],
                {/foreach}
            ]);

            let options = {
                title: 'Monthly Network Traffic',
                subtitle: 'over last 12 months',
                vAxis: { format: '###.## {$monthlyLargestPrefix}' }
            };

            let chart = new google.charts.Bar(document.getElementById('monthlyNetworkTrafficGraph'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }
    </script>
