<!DOCTYPE html>
<html lang="en">
<head>
    <title>Network Traffic</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages': ['bar']});
        google.charts.setOnLoadCallback(drawHourlyChart);
        google.charts.setOnLoadCallback(drawDailyChart);
        google.charts.setOnLoadCallback(drawMonthlyChart);

        function drawHourlyChart()
        {
            let data = google.visualization.arrayToDataTable([
                ['Hour', 'Traffic In', 'Traffic Out', 'Total Traffic'],
                <?php
                $hourlyGraph = $app->getVnstatData("hourlyGraph", $thisInterface);

                $hourlyLargestValue = $app->getLargestValue($hourlyGraph);
                $hourlyLargestPrefix = $app->getLargestPrefix($hourlyLargestValue);

                for ($i = 0; $i < count($hourlyGraph); $i++) {
                    $hour = $hourlyGraph[$i]['label'];
                    $inTraffic = kbytesToString($hourlyGraph[$i]['rx'], true, $hourlyLargestPrefix);
                    $outTraffic = kbytesToString($hourlyGraph[$i]['tx'], true, $hourlyLargestPrefix);
                    $totalTraffic = kbytesToString($hourlyGraph[$i]['total'], true, $hourlyLargestPrefix);

                    if (($hourlyGraph[$i]['label'] == "12am") && ($hourlyGraph[$i]['time'] == "0")) {
                        continue;
                    }

                    if ($i == 23) {
                        echo("['" . $hour . "', " . $inTraffic . " , " . $outTraffic . ", " . $totalTraffic . "]\n");
                    } else {
                        echo("['" . $hour . "', " . $inTraffic . " , " . $outTraffic . ", " . $totalTraffic . "],\n");
                    }
                }
                ?>
            ]);

            let options = {
                title: 'Hourly Network Traffic',
                subtitle: 'over last 24 hours',
                vAxis: {format: '##.## <?php echo $hourlyLargestPrefix; ?>'}
            };

            let chart = new google.charts.Bar(document.getElementById('hourlyNetworkTrafficGraph'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }

        function drawDailyChart()
        {
            let data = google.visualization.arrayToDataTable([
                ['Day', 'Traffic In', 'Traffic Out', 'Total Traffic'],
                <?php
                $dailyGraph = $app->getVnstatData("dailyGraph", $thisInterface);

                $dailyLargestValue = $app->getLargestValue($dailyGraph);
                $dailyLargestPrefix = $app->getLargestPrefix($dailyLargestValue);

                for ($i = 0; $i < count($dailyGraph); $i++) {
                    $day = $dailyGraph[$i]['label'];
                    $inTraffic = kbytesToString($dailyGraph[$i]['rx'], true, $dailyLargestPrefix);
                    $outTraffic = kbytesToString($dailyGraph[$i]['tx'], true, $dailyLargestPrefix);
                    $totalTraffic = kbytesToString($dailyGraph[$i]['total'], true, $dailyLargestPrefix);

                    if ($dailyGraph[$i]['time'] == "0") {
                        continue;
                    }

                    if ($i == 29) {
                        echo("['" . $day . "', " . $inTraffic . " , " . $outTraffic . ", " . $totalTraffic . "]\n");
                    } else {
                        echo("['" . $day . "', " . $inTraffic . " , " . $outTraffic . ", " . $totalTraffic . "],\n");
                    }
                }
                ?>
            ]);

            let options = {
                title: 'Daily Network Traffic',
                subtitle: 'over last 29 days (most recent first)',
                vAxis: {format: '##.## <?php echo $dailyLargestPrefix; ?>'}
            };

            let chart = new google.charts.Bar(document.getElementById('dailyNetworkTrafficGraph'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }

        function drawMonthlyChart()
        {
            let data = google.visualization.arrayToDataTable([
                ['Month', 'Traffic In', 'Traffic Out', 'Total Traffic'],
                <?php
                $monthlyGraph = $app->getVnstatData("monthlyGraph", $thisInterface);

                $monthlyLargestValue = $app->getLargestValue($monthlyGraph);
                $monthlyLargestPrefix = $app->getLargestPrefix($monthlyLargestValue);

                for ($i = 0; $i < count($monthlyGraph); $i++) {
                    $hour = $monthlyGraph[$i]['label'];
                    $inTraffic = kbytesToString($monthlyGraph[$i]['rx'], true, $monthlyLargestPrefix);
                    $outTraffic = kbytesToString($monthlyGraph[$i]['tx'], true, $monthlyLargestPrefix);
                    $totalTraffic = kbytesToString($monthlyGraph[$i]['total'], true, $monthlyLargestPrefix);

                    if ($i == 23) {
                        echo("['" . $hour . "', " . $inTraffic . " , " . $outTraffic . ", " . $totalTraffic . "]\n");
                    } else {
                        echo("['" . $hour . "', " . $inTraffic . " , " . $outTraffic . ", " . $totalTraffic . "],\n");
                    }
                }
                ?>
            ]);

            let options = {
                title: 'Monthly Network Traffic',
                subtitle: 'over last 12 months',
                vAxis: {format: '##.## <?php echo $monthlyLargestPrefix; ?>'}
            };

            let chart = new google.charts.Bar(document.getElementById('monthlyNetworkTrafficGraph'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }
    </script>
</head>
<body>
<div class="container">
    <div class="page-header">
        <h1>Network Traffic (<?php echo $app->getInterfaceName($thisInterface); ?>)</h1> <?php $render->printOptions(); ?>
    </div>
</div>

<div id="graphTabNav" class="container">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#hourlyGraph" data-toggle="tab">Hourly Graph</a></li>
        <li><a href="#dailyGraph" data-toggle="tab">Daily Graph</a></li>
        <li><a href="#monthlyGraph" data-toggle="tab">Monthly Graph</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="hourlyGraph">
            <div id="hourlyNetworkTrafficGraph" style="height: 300px;"></div>
        </div>

        <div class="tab-pane" id="dailyGraph">
            <div id="dailyNetworkTrafficGraph" style="height: 300px;"></div>
        </div>

        <div class="tab-pane" id="monthlyGraph">
            <div id="monthlyNetworkTrafficGraph" style="height: 300px;"></div>
        </div>
    </div>
</div>

<div id="tabNav" class="container">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#hourly" data-toggle="tab">Hourly</a></li>
        <li><a href="#daily" data-toggle="tab">Daily</a></li>
        <li><a href="#monthly" data-toggle="tab">Monthly</a></li>
        <li><a href="#top10" data-toggle="tab">Top 10</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="hourly">
            <?php $render->printTableStats("hourly", $thisInterface, 'hour') ?>
        </div>
        <div class="tab-pane" id="daily">
            <?php $render->printTableStats("daily", $thisInterface, 'Day') ?>
        </div>
        <div class="tab-pane" id="monthly">
            <?php $render->printTableStats("monthly", $thisInterface, 'Month') ?>
        </div>
        <div class="tab-pane" id="top10">
            <?php $render->printTableStats("top10", $thisInterface, 'Day') ?>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <span class="text-muted">Copyright (C) <?php echo date("Y"); ?> Alexander Marston -
            <a href="https://github.com/alexandermarston/vnstat-dashboard">vnstat-dashboard</a></span>
    </div>
</footer>
</body>
</html>
