<?php
require('config.php'); // Include all the configuration information
require('vnstat.php'); // The vnstat information parser
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Network Traffic</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <div class="page-header">
                <h1>Network Traffic (<?php echo $interface; ?>)</h1>
            </div>
            
            <h2>Daily</h2>
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
                    $daily = get_vnstat_data($vnstat_bin_dir, "daily", $interface);

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

            <h2>Monthly</h2>
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
                    $monthly = get_vnstat_data($vnstat_bin_dir, "monthly", $interface);
                    
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

            <h2>Top 10</h2>
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
                    $top10 = get_vnstat_data($vnstat_bin_dir, "top10", $interface);

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
    </body>
</html>