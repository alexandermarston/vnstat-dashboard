<?php

// Require includes
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/includes/vnstat.php';
require __DIR__ . '/includes/config.php';

// Initiate Smarty
$smarty = new Smarty();

// Set the current year
$smarty->assign('year', date("Y"));

// Set the current interface
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


$smarty->assign('current_interface', $thisInterface);

// Assign interface options
global $interface_list;
$smarty->assign('interface_list', $interface_list);

// Populate table data
$hourlyData = getVnstatData($vnstat_bin_dir, 'hourly', $thisInterface);
$smarty->assign('hourlyTableData', $hourlyData);

$dailyData = getVnstatData($vnstat_bin_dir, 'daily', $thisInterface);
$smarty->assign('dailyTableData', $dailyData);

$monthlyData = getVnstatData($vnstat_bin_dir, 'monthly', $thisInterface);
$smarty->assign('monthlyTableData', $monthlyData);

$top10Data = getVnstatData($vnstat_bin_dir, 'top10', $thisInterface);
$smarty->assign('top10TableData', $top10Data);

// Populate graph data
$hourlyGraphData = getVnstatData($vnstat_bin_dir, 'hourlyGraph', $thisInterface);
$hourlyLargestValue = getLargestValue($hourlyGraphData);
$hourlyLargestPrefix = getLargestPrefix($hourlyLargestValue);
$smarty->assign('hourlyGraphData', $hourlyGraphData);
$smarty->assign('hourlyLargestPrefix', $hourlyLargestPrefix);

$dailyGraphData = getVnstatData($vnstat_bin_dir, 'dailyGraph', $thisInterface);
$dailyLargestValue = getLargestValue($dailyGraphData);
$dailyLargestPrefix = getLargestPrefix($dailyLargestValue);
$smarty->assign('dailyGraphData', $dailyGraphData);
$smarty->assign('dailyLargestPrefix', $dailyLargestPrefix);

$monthlyGraphData = getVnstatData($vnstat_bin_dir, 'monthlyGraph', $thisInterface);
$monthlyLargestValue = getLargestValue($monthlyGraphData);
$monthlyLargestPrefix = getLargestPrefix($monthlyLargestValue);
$smarty->assign('monthlyGraphData', $monthlyGraphData);
$smarty->assign('monthlyLargestPrefix', $monthlyLargestPrefix);

// Display the page
$smarty->display('templates/site_index.tpl');

?>
