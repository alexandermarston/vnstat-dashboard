<?php
/*
 * Copyright (C) 2019 Alexander Marston (alexander.marston@gmail.com)
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

require('includes/vnstat.php'); // The vnstat information parser
require('includes/config.php'); // Include all the configuration information

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
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
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
    <div class="pb-2 mt-4 mb-2 border-bottom">
        <h1>Network Traffic (<?php echo $interface_name[$thisInterface]; ?>)</h1> <?php printOptions(); ?>
    </div>
</div>

<div class="container">
    <ul class="nav nav-tabs" id="graphTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="hourly-graph-tab" data-toggle="tab" href="#hourly-graph" role="tab" aria-controls="hourly-graph" aria-selected="true">Hourly Graph</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="daily-graph-tab" data-toggle="tab" href="#daily-graph" role="tab" aria-controls="daily-graph" aria-selected="false">Daiily Graph</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="monthly-graph-tab" data-toggle="tab" href="#monthly-graph" role="tab" aria-controls="monthly-graph" aria-selected="false">Monthly Graph</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="hourly-graph" role="tabpanel" aria-labelledby="hourly-graph-tab">
            <div id="hourlyNetworkTrafficGraph" style="height: 300px;"></div>
        </div>

        <div class="tab-pane fade" id="daily-graph" role="tabpanel" aria-labelledby="daily-graph-tab">
            <div id="dailyNetworkTrafficGraph" style="height: 300px;"></div>
        </div>

        <div class="tab-pane fade" id="monthly-graph" role="tabpanel" aria-labelledby="monthly-graph-tab">
            <div id="monthlyNetworkTrafficGraph" style="height: 300px;"></div>
        </div>
    </div>
</div>

<div class="container">
    <ul class="nav nav-tabs" id="tableTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="hourly-table-tab" data-toggle="tab" href="#hourly-table" role="tab" aria-controls="hourly-table" aria-selected="true">Hourly</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="daily-table-tab" data-toggle="tab" href="#daily-table" role="tab" aria-controls="daily-table" aria-selected="false">Daiily</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="monthly-table-tab" data-toggle="tab" href="#monthly-table" role="tab" aria-controls="monthly-table" aria-selected="false">Monthly</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="top10-table-tab" data-toggle="tab" href="#top10=table" role="tab" aria-controls="top10-table" aria-selected="false">Top 10</a>
        </li>
    </ul>

    <div class="tab-content" id="tableTabContent">
        <div class="tab-pane fade show active" id="hourly-table" role="tabpanel" aria-labelledby="hourly-table-tab">
            <?php printTableStats($vnstat_bin_dir, "hourly", $thisInterface, 'Hour') ?>
        </div>
        <div class="tab-pane fade" id="daily-table" role="tabpanel" aria-labelledby="daily-table-tab">
            <?php printTableStats($vnstat_bin_dir, "daily", $thisInterface, 'Day') ?>
        </div>
        <div class="tab-pane fade" id="monthly-table" role="tabpanel" aria-labelledby="monthly-table-tab">
            <?php printTableStats($vnstat_bin_dir, "monthly", $thisInterface, 'Month') ?>
        </div>
        <div class="tab-pane fade" id="top-10-table" role="tabpanel" aria-labelledby="top10-table-tab">
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
