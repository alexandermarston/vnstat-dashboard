<?php
/*
 * Copyright (C) 2016 Alexander Marston (alexander.marston@gmail.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require('vnstat.php'); // The vnstat information parser
require('config.php'); // Include all the configuration information

function printOptions()
{
    global $interface_list;

    $i = 0;
    foreach ($interface_list as $interface) {
        $i++;
        if ($i == count($interface_list)) {
            echo "<a href=\"?i=" . rawurlencode($interface) . "\">" . rawurlencode($interface) . "</a>";
        } else {
            echo "<a href=\"?i=" . rawurlencode($interface) . "\">" . rawurlencode($interface) . ", </a>";
        }
    }
}

function printTableStats($path, $type, $interface, $label)
{
    echo '<table class="table table-bordered">
        <thead>
        <tr>
            <th>' . $label . '</th>
            <th>Received</th>
            <th>Sent</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>';
    $data = getVnstatData($path, $type, $interface);

    for ($i = 0; $i < count($data); $i++) {
        $label = $data[$i]['label'];
        $totalReceived = $data[$i]['rx'];
        $totalSent = $data[$i]['tx'];
        $totalTraffic = $data[$i]['total'];
        echo '<tr>';
        echo '<td>' . $label . '</td>';
        echo '<td>' . $totalReceived . '</td>';
        echo '<td>' . $totalSent . '</td>';
        echo '<td>' . $totalTraffic . '</td>';
        echo '</tr>';

    }
    echo '</tbody></table>';
}

$thisInterface = "";

if (isset($_GET['i'])) {
    $interfaceChosen = rawurldecode($_GET['i']);
    if (in_array($interfaceChosen, $interface_list, true)) {
        $thisInterface = $interfaceChosen;
    } else {
        $thisInterface = reset($interface_list);
    }
} else {
    // Assume they mean the first interface
    $thisInterface = reset($interface_list);
}
?>
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
                $hourlyGraph = getVnstatData($vnstat_bin_dir, "hourlyGraph", $thisInterface);

                $hourlyLargestValue = getLargestValue($hourlyGraph);
                $hourlyLargestPrefix = getLargestPrefix($hourlyLargestValue);

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
                $dailyGraph = getVnstatData($vnstat_bin_dir, "dailyGraph", $thisInterface);

                $dailyLargestValue = getLargestValue($dailyGraph);
                $dailyLargestPrefix = getLargestPrefix($dailyLargestValue);

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
                $monthlyGraph = getVnstatData($vnstat_bin_dir, "monthlyGraph", $thisInterface);

                $monthlyLargestValue = getLargestValue($monthlyGraph);
                $monthlyLargestPrefix = getLargestPrefix($monthlyLargestValue);

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
        <h1>Network Traffic (<?php echo $interface_name[$thisInterface]; ?>)</h1> <?php printOptions(); ?>
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
            <?php printTableStats($vnstat_bin_dir, "hourly", $thisInterface, 'hour') ?>
        </div>
        <div class="tab-pane" id="daily">
            <?php printTableStats($vnstat_bin_dir, "daily", $thisInterface, 'Day') ?>
        </div>
        <div class="tab-pane" id="monthly">
            <?php printTableStats($vnstat_bin_dir, "monthly", $thisInterface, 'Month') ?>
        </div>
        <div class="tab-pane" id="top10">
            <?php printTableStats($vnstat_bin_dir, "top10", $thisInterface, 'Day') ?>
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
