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

// Require includes
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/includes/vnstat.php';
require __DIR__ . '/includes/utilities.php';
require __DIR__ . '/includes/config.php';

// Initiaite vnStat class
$vnstat = new vnStat($vnstat_bin_dir);

// Initiate Smarty
$smarty = new Smarty();

// Set the current year
$smarty->assign('year', date("Y"));

// Set the list of interfaces
$interface_list = $vnstat->getInterfaces();

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
$smarty->assign('interface_list', $vnstat->getInterfaces());

// Populate table data
$hourlyData = $vnstat->getInterfaceData('hourly', 'table', $thisInterface);
$smarty->assign('hourlyTableData', $hourlyData);

$dailyData = $vnstat->getInterfaceData('daily', 'table', $thisInterface);
$smarty->assign('dailyTableData', $dailyData);

$monthlyData = $vnstat->getInterfaceData('monthly', 'table', $thisInterface);
$smarty->assign('monthlyTableData', $monthlyData);

$top10Data = $vnstat->getInterfaceData('top10', 'table', $thisInterface);
$smarty->assign('top10TableData', $top10Data);

// Populate graph data
$hourlyGraphData = $vnstat->getInterfaceData('hourly', 'graph', $thisInterface);
$smarty->assign('hourlyGraphData', $hourlyGraphData);
$smarty->assign('hourlyLargestPrefix', $hourlyGraphData[1]['delimiter']);

$dailyGraphData = $vnstat->getInterfaceData('daily', 'graph', $thisInterface);
$smarty->assign('dailyGraphData', $dailyGraphData);
$smarty->assign('dailyLargestPrefix', $dailyGraphData[1]['delimiter']);

$monthlyGraphData = $vnstat->getInterfaceData('monthly', 'graph', $thisInterface);
$smarty->assign('monthlyGraphData', $monthlyGraphData);
$smarty->assign('monthlyLargestPrefix', $monthlyGraphData[1]['delimiter']);

// Display the page
$smarty->display('templates/site_index.tpl');

?>
