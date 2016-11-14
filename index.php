<?php
require('vnstat.php'); // The vnstat information parser
require('config.php'); // Include all the configuration information

function print_options() {
    global $interface_list;

    $i = 0;
    foreach ($interface_list as $interface) {
        $i++;
        if ($i == count($interface_list)) {
            echo "<a href=\"?i=" . $interface . "\">" . $interface . "</a>";
        } else {
            echo "<a href=\"?i=" . $interface . "\">" . $interface . ", </a>";
        }
    }
}

$thisInterface = "";

if (isset($_GET['i'])) {
    $interfaceChosen = $_GET['i'];
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

        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
        <script type="text/javascript">
            google.charts.load('current', {'packages': ['bar']});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Hour', 'Total Traffic'],
                    <?php
                    $hourly = get_vnstat_data($vnstat_bin_dir, "hourly", $thisInterface);

                    for ($i = 0; $i < count($hourly); $i++) {
                        $hour = $hourly[$i]['label'];
                        $totalTraffic = $hourly[$i]['total'];

                        if ($i == 23) {
                            echo("['" . $hour . "', " . $totalTraffic . "]\n");
                        } else {
                            echo("['" . $hour . "', " . $totalTraffic . "],\n");
                        }
                    }
                    ?>
                ]);

                var options = {
                    title: 'Hourly Network Traffic',
                    subtitle: 'over last 24 hours',
                    vAxis: {format: '##.## MB'}
                };

                var chart = new google.charts.Bar(document.getElementById('hourlyNetworkTrafficGraph'));
                chart.draw(data, google.charts.Bar.convertOptions(options));
            }
        </script>
    </head>
    <body>
        <div class="container">
            <div class="page-header">
                <h1>Network Traffic (<?php echo $interface_name[$thisInterface]; ?>)</h1> <?php print_options(); ?>
            </div>

            <div id="hourlyNetworkTrafficGraph" style="height: 300px;"></div>

        </div>

        <div id="tabNav" class="container">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#daily" data-toggle="tab">Daily</a></li>
                <li><a href="#monthly" data-toggle="tab">Monthly</a></li>
                <li><a href="#top10" data-toggle="tab">Top 10</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="daily">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Received</th>
                                <th>Sent</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $daily = get_vnstat_data($vnstat_bin_dir, "daily", $thisInterface);

                            for ($i = 0; $i < count($daily); $i++) {
                                if ($daily[$i]['act'] == 1) {
                                    $day = $daily[$i]['label'];
                                    $totalReceived = $daily[$i]['rx'];
                                    $totalSent = $daily[$i]['tx'];
                                    $totalTraffic = $daily[$i]['total'];
                                    ?>
                                    <tr>
                                        <td><?php echo $day; ?></td>
                                        <td><?php echo $totalReceived; ?></td>
                                        <td><?php echo $totalSent; ?></td>
                                        <td><?php echo $totalTraffic; ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane" id="monthly">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Received</th>
                                <th>Sent</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $monthly = get_vnstat_data($vnstat_bin_dir, "monthly", $thisInterface);

                            for ($i = 0; $i < count($monthly); $i++) {
                                if ($monthly[$i]['act'] == 1) {
                                    $month = $monthly[$i]['label'];
                                    $totalReceived = $monthly[$i]['rx'];
                                    $totalSent = $monthly[$i]['tx'];
                                    $totalTraffic = $monthly[$i]['total'];
                                    ?>
                                    <tr>
                                        <td><?php echo $month; ?></td>
                                        <td><?php echo $totalReceived; ?></td>
                                        <td><?php echo $totalSent; ?></td>
                                        <td><?php echo $totalTraffic; ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane" id="top10">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Received</th>
                                <th>Sent</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $top10 = get_vnstat_data($vnstat_bin_dir, "top10", $thisInterface);

                            for ($i = 0; $i < count($top10); $i++) {
                                if ($top10[$i]['act'] == 1) {
                                    $day = $top10[$i]['label'];
                                    $totalReceived = $top10[$i]['rx'];
                                    $totalSent = $top10[$i]['tx'];
                                    $totalTraffic = $top10[$i]['total'];
                                    ?>
                                    <tr>
                                        <td><?php echo $day; ?></td>
                                        <td><?php echo $totalReceived; ?></td>
                                        <td><?php echo $totalSent; ?></td>
                                        <td><?php echo $totalTraffic; ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>