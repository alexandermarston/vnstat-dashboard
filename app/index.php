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

if (isset($vnstat_config)) {
    $vnstat_cmd = $vnstat_bin_dir.' --config '.$vnstat_config;
} else {
    $vnstat_cmd = $vnstat_bin_dir;
}

$smarty->assign('current_interface', $thisInterface);

// Assign interface options
global $interface_list;
$smarty->assign('interface_list', $interface_list);

// Populate table data
$fiveData = getVnstatData($vnstat_cmd, 'five', $thisInterface);
$smarty->assign('fiveTableData', $fiveData);

$hourlyData = getVnstatData($vnstat_cmd, 'hourly', $thisInterface);
$smarty->assign('hourlyTableData', $hourlyData);

$dailyData = getVnstatData($vnstat_cmd, 'daily', $thisInterface);
$smarty->assign('dailyTableData', $dailyData);

$monthlyData = getVnstatData($vnstat_cmd, 'monthly', $thisInterface);
$smarty->assign('monthlyTableData', $monthlyData);

$top10Data = getVnstatData($vnstat_cmd, 'top10', $thisInterface);
$smarty->assign('top10TableData', $top10Data);

// Populate graph data

$fiveGraphData = getVnstatData($vnstat_cmd, 'fiveGraph', $thisInterface);
$fiveLargestValue = getLargestValue($fiveGraphData);
$fiveMagnitude = getMagnitude($fiveLargestValue);
$fiveLargestPrefix = getLargestPrefix($fiveMagnitude);
$fiveBase = getBaseValue($fiveGraphData, $fiveMagnitude);
if ($fiveBase < .01)
{
    $fiveMagnitude = $fiveMagnitude - 1;
    $fiveLargestPrefix = getLargestPrefix($fiveMagnitude);
    $fiveBase = getBaseValue($fiveGraphData, $fiveMagnitude);
}
$smarty->assign('fiveBase', $fiveBase);
$smarty->assign('fiveGraphData', $fiveGraphData);
$smarty->assign('fiveLargestPrefix', $fiveLargestPrefix);

$hourlyGraphData = getVnstatData($vnstat_cmd, 'hourlyGraph', $thisInterface);
$hourlyLargestValue = getLargestValue($hourlyGraphData);
$hourlyMagnitude = getMagnitude($hourlyLargestValue);
$hourlyLargestPrefix = getLargestPrefix($hourlyMagnitude);
$hourlyBase = getBaseValue($hourlyGraphData, $hourlyMagnitude);
if ($hourlyBase < .01)
{
    $hourlyMagnitude = $hourlyMagnitude - 1;
    $hourlyLargestPrefix = getLargestPrefix($hourlyMagnitude);
    $hourlyBase = getBaseValue($hourlyGraphData, $hourlyMagnitude);
}
$smarty->assign('hourlyBase', $hourlyBase);
$smarty->assign('hourlyGraphData', $hourlyGraphData);
$smarty->assign('hourlyLargestPrefix', $hourlyLargestPrefix);

$dailyGraphData = getVnstatData($vnstat_cmd, 'dailyGraph', $thisInterface);
$dailyLargestValue = getLargestValue($dailyGraphData);
$dailyMagnitude = getMagnitude($dailyLargestValue);
$dailyLargestPrefix = getLargestPrefix($dailyMagnitude);
$dailyBase = getBaseValue($dailyGraphData, $dailyMagnitude);
if ($dailyBase < .01)
{
    $dailyMagnitude = $dailyMagnitude - 1;
    $dailyLargestPrefix = getLargestPrefix($dailyMagnitude);
    $dailyBase = getBaseValue($dailyGraph, $dailyMagnitude);
}
$smarty->assign('dailyBase', $dailyBase);
$smarty->assign('dailyGraphData', $dailyGraphData);
$smarty->assign('dailyLargestPrefix', $dailyLargestPrefix);

$monthlyGraphData = getVnstatData($vnstat_cmd, 'monthlyGraph', $thisInterface);
$monthlyLargestValue = getLargestValue($monthlyGraphData);
$monthlyMagnitude = getMagnitude($monthlyLargestValue);
$monthlyLargestPrefix = getLargestPrefix($monthlyMagnitude);
$monthlyBase = getBaseValue($monthlyGraphData, $monthlyMagnitude);
if ($monthlyBase < .01)
{
    $monthlyMagnitude = $monthlyMagnitude - 1;
    $monthlyLargestPrefix = getLargestPrefix($monthlyMagnitude);
    $monthlyBase = getBaseValue($monthlyGraph, $monthlyMagnitude);
}
$smarty->assign('monthlyBase', $monthlyBase);
$smarty->assign('monthlyGraphData', $monthlyGraphData);
$smarty->assign('monthlyLargestPrefix', $monthlyLargestPrefix);

// Display the page
$smarty->display('templates/site_index.tpl');

?>
