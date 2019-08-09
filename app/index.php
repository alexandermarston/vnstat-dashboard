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

if (isset($vnstat_config)) {
    $vnstat_cmd = $vnstat_bin_dir.' --config '.$vnstat_config;
} else {
    $vnstat_cmd = $vnstat_bin_dir;
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
        $width = getCookie("width");
        if (!$width || ($width != window.innerWidth)) {
            //console.log(window.innerWidth);
            createCookie("width", window.innerWidth, "1");
            document.location.reload();
        }

        google.charts.load('current', {'packages': ['corechart']});
        google.charts.load('current', {'packages': ['bar']});
        google.charts.setOnLoadCallback(drawFiveChart);
        google.charts.setOnLoadCallback(drawHourlyChart);
        google.charts.setOnLoadCallback(drawDailyChart);
        google.charts.setOnLoadCallback(drawMonthlyChart);

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
                [{type: 'datetime', label: 'Time'}, 'Traffic In', 'Traffic Out', 'Total Traffic'],
                <?php
                $width = 800;
                if (isset($_COOKIE["width"])) {
                    $width = $_COOKIE["width"];
                }

                // get duration in seconds for initial display based on screen width
                $duration = floor(($width-200)/16); // number of samples
                if ($duration < 24) { $duration = 24; } // minimum of 24 bar groups = 2 hours
                $duration = $duration * 300; // in seconds

                $fiveGraph = getVnstatData($vnstat_cmd, "fiveGraph", $thisInterface);

                $fiveLargestValue = getLargestValue($fiveGraph);
                $fiveMagnitude = getMagnitude($fiveLargestValue);
                $fiveLargestPrefix = getLargestPrefix($fiveMagnitude);
                $fiveBase = getBaseValue($fiveGraph, $fiveMagnitude);
                if ($fiveBase < .01)
                {
                    $fiveMagnitude = $fiveMagnitude - 1;
                    $fiveLargestPrefix = getLargestPrefix($fiveMagnitude);
                    $fiveBase = getBaseValue($fiveGraph, $fiveMagnitude);
                }
                $first = true;
                $lastSample = 0;

                for ($i = 0; $i < count($fiveGraph); $i++) {
                    $time = $fiveGraph[$i]['label'];
                    $inTraffic = bytesToString($fiveGraph[$i]['rx'], true, $fiveMagnitude);
                    $outTraffic = bytesToString($fiveGraph[$i]['tx'], true, $fiveMagnitude);
                    $totalTraffic = bytesToString($fiveGraph[$i]['total'], true, $fiveMagnitude);

                    // can't just use duration/300 - there might be fewer or missing samples
                    if ($fiveGraph[0]['time'] - $fiveGraph[$i]['time'] < $duration) {
                        $lastSample = $i;
                    }

                    if ($first) {
                        $first = false;
                    } else {
                        echo(",\n");
                    }
                    echo("['" . $time . "', " . $inTraffic . ", " . $outTraffic . ", " . $totalTraffic . "]");
                }
                echo("\n");
                ?>
            ]);

            var options = {
                title: 'Five minute Network Traffic',
                subtitle: 'over last 6 hours',
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
                    title: <?php if ($lastSample < count($fiveGraph) - 1) { 
                        echo "'Hour:Minute   (Scroll to zoom, Drag to pan)'";
                        } else {
                        echo "'Hour:Minute'";
                        } ?>
                    <?php if (count($fiveGraph) > 0) {
                        echo ", viewWindow: {";
                        echo "min: new " . $fiveGraph[$lastSample]['label'] . ", ";
                        echo "max: new " . $fiveGraph[0]['label'];
                        echo "}";
                    } ?>
                },
                vAxis: {
                    format: '###.####<?php echo $fiveLargestPrefix; ?>',
                    textStyle: { fontSize: 12 },
                    scaleType: 'log',
                    baseline: <?php echo $fiveBase; ?>
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
                [{type: 'datetime', label: 'Hour'}, 'Traffic In', 'Traffic Out', 'Total Traffic'],
                <?php
                $width = 800;
                if (isset($_COOKIE["width"])) {
                    $width = $_COOKIE["width"];
                }

                // get duration in seconds for initial display based on screen width
                $duration = floor(($width-200)/32); // number of samples
                if ($duration < 24) { $duration = 24; } // minimum of 24 bar groups = 1 day
                $duration = $duration * 3600; // in seconds

                $hourlyGraph = getVnstatData($vnstat_cmd, "hourlyGraph", $thisInterface);

                $hourlyLargestValue = getLargestValue($hourlyGraph);
                $hourlyMagnitude = getMagnitude($hourlyLargestValue);
                $hourlyLargestPrefix = getLargestPrefix($hourlyMagnitude);
                $hourlyBaseValue = getBaseValue($hourlyGraph, $hourlyMagnitude);
                if ($hourlyBaseValue < .01)
                {
                    $hourlyMagnitude = $hourlyMagnitude - 1;
                    $hourlyLargestPrefix = getLargestPrefix($hourlyMagnitude);
                    $hourlyBaseValue = getBaseValue($hourlyGraph, $hourlyMagnitude);
                }
                $lastSample = 0;

                for ($i = 0; $i < count($hourlyGraph); $i++) {
                    $hour = $hourlyGraph[$i]['label'];
                    $inTraffic = bytesToString($hourlyGraph[$i]['rx'], true, $hourlyMagnitude);
                    $outTraffic = bytesToString($hourlyGraph[$i]['tx'], true, $hourlyMagnitude);
                    $totalTraffic = bytesToString($hourlyGraph[$i]['total'], true, $hourlyMagnitude);

                    // can't just use duration/3600 - there might be fewer or missing samples
                    if ($hourlyGraph[0]['time'] - $hourlyGraph[$i]['time'] < $duration) {
                        $lastSample = $i;
                    }

                    if ($i == count($hourlyGraph) - 1) {
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
                    title: <?php if ($lastSample < count($fiveGraph) - 1) { 
                        echo "'Day:Hour (Scroll to zoom, Drag to pan)'";
                        } else {
                        echo "'Day:Hour'";
                        } ?>,
                    viewWindow: {
                        min: new <?php echo $hourlyGraph[$lastSample]['label']; ?>,
                        max: new <?php echo $hourlyGraph[0]['label']; ?>
                    }
                },
                vAxis: {
                    format: '###.####<?php echo $hourlyLargestPrefix; ?>',
                    textStyle: { fontSize: 12 },
                    scaleType: 'log',
                    baseline: <?php echo $hourlyBaseValue; ?>
                }
            };

            let chart = new google.visualization.BarChart(document.getElementById('hourlyNetworkTrafficGraph'));
            chart.draw(data, options);
        }

        function drawDailyChart()
        {
            let data = google.visualization.arrayToDataTable([
                [{type: 'datetime', label: 'Date'}, 'Traffic In', 'Traffic Out', 'Total Traffic'],
                <?php
                $width = 800;
                if (isset($_COOKIE["width"])) {
                    $width = $_COOKIE["width"];
                }

                // get duration in seconds for initial display based on screen width
                $duration = floor(($width-200)/32); // number of samples
                if ($duration < 30) { $duration = 30; } // minimum of 30 bar groups = 1 month
                $duration = $duration * 3600 * 24; // in seconds

                $dailyGraph = getVnstatData($vnstat_cmd, "dailyGraph", $thisInterface);

                $dailyLargestValue = getLargestValue($dailyGraph);
                $dailyMagnitude = getMagnitude($dailyLargestValue);
                $dailyLargestPrefix = getLargestPrefix($dailyMagnitude);
                $dailyBaseValue = getBaseValue($dailyGraph, $dailyMagnitude);
                if ($dailyBaseValue < .01)
                {
                    $dailyMagnitude = $dailyMagnitude - 1;
                    $dailyLargestPrefix = getLargestPrefix($dailyMagnitude);
                    $dailyBaseValue = getBaseValue($dailyGraph, $dailyMagnitude);
                }
                $lastSample = 0;

                for ($i = 0; $i < count($dailyGraph); $i++) {
                    $day = $dailyGraph[$i]['label'];
                    $inTraffic = bytesToString($dailyGraph[$i]['rx'], true, $dailyMagnitude);
                    $outTraffic = bytesToString($dailyGraph[$i]['tx'], true, $dailyMagnitude);
                    $totalTraffic = bytesToString($dailyGraph[$i]['total'], true, $dailyMagnitude);

                    // can't just use duration/x - there might be fewer or missing samples
                    if ($dailyGraph[0]['time'] - $dailyGraph[$i]['time'] < $duration) {
                        $lastSample = $i;
                    }

                    if ($i == count($dailyGraph)- 1) {
                        echo("['" . $day . "', " . $inTraffic . " , " . $outTraffic . ", " . $totalTraffic . "]\n");
			break;
                    } else {
                        echo("['" . $day . "', " . $inTraffic . " , " . $outTraffic . ", " . $totalTraffic . "],\n");
                    }
                }
                ?>
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
                    //minorGridlines: { count: 0 },
                    title: 'Month/Day  Scroll to zoom, Drag to pan',
                    title: <?php if ($lastSample < count($fiveGraph) - 1) { 
                        echo "'Month/Day (Scroll to zoom, Drag to pan)'";
                        } else {
                        echo "'Month/Day'";
                        } ?>,
                    viewWindow: {
                        min: new <?php echo $dailyGraph[$lastSample]['label']; ?>,
                        max: new <?php echo $dailyGraph[0]['label']; ?>
                    }
                },
                vAxis: {
                    format: '###.####<?php echo $dailyLargestPrefix; ?>',
                    textStyle: { fontSize: 12 },
                    scaleType: 'log',
                    baseline: <?php echo $dailyBaseValue; ?>
                }
            };

            let chart = new google.visualization.BarChart(document.getElementById('dailyNetworkTrafficGraph'));
            chart.draw(data, options);
        }

        function drawMonthlyChart()
        {
            let data = google.visualization.arrayToDataTable([
                ['Month', 'Traffic In', 'Traffic Out', 'Total Traffic'],
                <?php
                $monthlyGraph = getVnstatData($vnstat_cmd, "monthlyGraph", $thisInterface);

                $monthlyLargestValue = getLargestValue($monthlyGraph);
                $monthlyMagnitude = getMagnitude($monthlyLargestValue);
                $monthlyLargestPrefix = getLargestPrefix($monthlyMagnitude);

                for ($i = 0; $i < count($monthlyGraph); $i++) {
                    $hour = $monthlyGraph[$i]['label'];
                    $inTraffic = bytesToString($monthlyGraph[$i]['rx'], true, $monthlyMagnitude);
                    $outTraffic = bytesToString($monthlyGraph[$i]['tx'], true, $monthlyMagnitude);
                    $totalTraffic = bytesToString($monthlyGraph[$i]['total'], true, $monthlyMagnitude);

                    if ($i == count($monthlyGraph) - 1) {
                        echo("['" . $hour . "', " . $inTraffic . " , " . $outTraffic . ", " . $totalTraffic . "]\n");
			break;
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
        <?php if ($version > 1) { echo "
        <li class=\"nav-item\">
            <a class=\"nav-link active\" id=\"five-graph-tab\" data-toggle=\"tab\" href=\"#five-graph\" role=\"tab\" aria-controls=\"five-graph\" aria-selected=\"true\">Five Minute Graph</a>
        </li>
        <li class=\"nav-item\">
            <a class=\"nav-link\" id=\"hourly-graph-tab\" data-toggle=\"tab\" href=\"#hourly-graph\" role=\"tab\" aria-controls=\"hourly-graph\" aria-selected=\"false\">Hourly Graph</a>
        </li>
        "; } else { echo "
        <li class=\"nav-item\">
            <a class=\"nav-link active\" id=\"hourly-graph-tab\" data-toggle=\"tab\" href=\"#hourly-graph\" role=\"tab\" aria-controls=\"hourly-graph\" aria-selected=\"true\">Hourly Graph</a>
        </li>
        "; } ?>
        <li class="nav-item">
            <a class="nav-link" id="daily-graph-tab" data-toggle="tab" href="#daily-graph" role="tab" aria-controls="daily-graph" aria-selected="false">Daily Graph</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="monthly-graph-tab" data-toggle="tab" href="#monthly-graph" role="tab" aria-controls="monthly-graph" aria-selected="false">Monthly Graph</a>
        </li>
    </ul>

    <div class="tab-content">
        <?php if ($version > 1) { echo "
        <div class=\"tab-pane fade show active\" id=\"five-graph\" role=\"tabpanel\" aria-labelledby=\"five-graph-tab\">
            <div id=\"fiveNetworkTrafficGraph\" style=\"height: 400px;\"></div>
        </div>

        <div class=\"tab-pane fade\" id=\"hourly-graph\" role=\"tabpanel\" aria-labelledby=\"hourly-graph-tab\">
            <div id=\"hourlyNetworkTrafficGraph\" style=\"height: 400px;\"></div>
        </div>
        "; } else { echo "
        <div class=\"tab-pane fade show active\" id=\"hourly-graph\" role=\"tabpanel\" aria-labelledby=\"hourly-graph-tab\">
            <div id=\"hourlyNetworkTrafficGraph\" style=\"height: 400px;\"></div>
        </div>
        "; } ?>

        <div class="tab-pane fade" id="daily-graph" role="tabpanel" aria-labelledby="daily-graph-tab">
            <div id="dailyNetworkTrafficGraph" style="height: 400px;"></div>
        </div>

        <div class="tab-pane fade" id="monthly-graph" role="tabpanel" aria-labelledby="monthly-graph-tab">
            <div id="monthlyNetworkTrafficGraph" style="height: 400px;"></div>
        </div>
    </div>
</div>

<div class="container">
    <ul class="nav nav-tabs" id="tableTab" role="tablist">
        <?php if ($version > 1) { echo "
        <li class=\"nav-item\">
            <a class=\"nav-link active\" id=\"five-table-tab\" data-toggle=\"tab\" href=\"#five-table\" role=\"tab\" aria-controls=\"five-table\" aria-selected=\"true\">Five Minute</a>
        </li> 
        <li class=\"nav-item\">
            <a class=\"nav-link\" id=\"hourly-table-tab\" data-toggle=\"tab\" href=\"#hourly-table\" role=\"tab\" aria-controls=\"hourly-table\" aria-selected=\"false\">Hourly</a>
        </li>
        "; } else { echo "
        <li class=\"nav-item\">
            <a class=\"nav-link active\" id=\"hourly-table-tab\" data-toggle=\"tab\" href=\"#hourly-table\" role=\"tab\" aria-controls=\"hourly-table\" aria-selected=\"true\">Hourly</a>
        </li>
        "; } ?>
        <li class="nav-item">
            <a class="nav-link" id="daily-table-tab" data-toggle="tab" href="#daily-table" role="tab" aria-controls="daily-table" aria-selected="false">Daiily</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="monthly-table-tab" data-toggle="tab" href="#monthly-table" role="tab" aria-controls="monthly-table" aria-selected="false">Monthly</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="top10-table-tab" data-toggle="tab" href="#top10-table" role="tab" aria-controls="top10-table" aria-selected="false">Top</a>
        </li>
    </ul>

    <div class="tab-content" id="tableTabContent">
        <?php if ($version > 1) { echo "
        <div class=\"tab-pane fade show active\" id=\"five-table\" role=\"tabpanel\" aria-labelledby=\"five-table-tab\">
            ";
            printTableStats($vnstat_cmd, "five", $thisInterface, 'Time');
            echo "
        </div>
        <div class=\"tab-pane fade\" id=\"hourly-table\" role=\"tabpanel\" aria-labelledby=\"hourly-table-tab\">
            ";
            printTableStats($vnstat_cmd, "hourly", $thisInterface, 'Hour');
            echo "
        </div>
        "; } else { echo "
        <div class=\"tab-pane fade show active\" id=\"hourly-table\" role=\"tabpanel\" aria-labelledby=\"hourly-table-tab\">
            ";
            printTableStats($vnstat_cmd, "hourly", $thisInterface, 'Hour');
            echo "
        </div>
        "; } ?>

        <div class="tab-pane fade" id="daily-table" role="tabpanel" aria-labelledby="daily-table-tab">
            <?php printTableStats($vnstat_cmd, "daily", $thisInterface, 'Day'); ?>
        </div>
        <div class="tab-pane fade" id="monthly-table" role="tabpanel" aria-labelledby="monthly-table-tab">
            <?php printTableStats($vnstat_cmd, "monthly", $thisInterface, 'Month'); ?>
        </div>
        <div class="tab-pane fade" id="top10-table" role="tabpanel" aria-labelledby="top10-table-tab">
            <?php printTableStats($vnstat_cmd, "top10", $thisInterface, 'Day'); ?>
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
