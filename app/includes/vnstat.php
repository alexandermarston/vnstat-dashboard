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

$logk = log(1024);
$terraB = pow(1024,4);
$version = 1;

function getMagnitude($bytes)
{
    global $logk;

    $ui = floor(round(log($bytes)/$logk,3));
    if ($ui < 0) { $ui = 0; }
    if ($ui > 4) { $ui = 4; }

    return $ui;
}

// $wSuf (without suffix MB, GB, etc)
function bytesToString($bytes, $wSuf = false, $magnitude = null)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    if (isset($magnitude)) {
        $ui = $magnitude;
    } else {
        $ui = getMagnitude($bytes);
    }

    if ($wSuf == true) {
        return sprintf("%0.8f", ($bytes / pow(1024, $ui)));
    } else {
        return sprintf("%0.2f %s", ($bytes / pow(1024, $ui)), $units[$ui]);
    }
}

function getVnstatInterfaces($path)
{
    $vnstat_interfaces = []; // Create an empty array

    $vnstatIF = popen("$path --iflist", "r");
    if (is_resource($vnstatIF)) {
        $iBuffer = '';
        while (!feof($vnstatIF)) {
            $iBuffer .= fgets($vnstatIF);
        }

        $vnstat_temp = preg_replace("/\s+/", " ", preg_replace("/\([^)]+\)/", "", trim(str_replace("Available interfaces: ", "", $iBuffer))));

        $vnstat_interfaces = explode(" ", $vnstat_temp);
        pclose($vnstatIF);
    }

    return $vnstat_interfaces;
}

function getLargestValue($array)
{
    return $max = array_reduce($array, function ($a, $b) {
        return $a > $b['total'] ? $a : $b['total'];
    });
}

function getBaseValue($array, $magnitude)
{
    global $terraB;

    $sml = array_reduce($array, function ($a, $b) {
        if  ((0 < $b['rx']) && ($b['rx'] < $b['tx'])) {
            $sml = $b['rx'];
        } else {
            $sml = $b['tx'];
        }
        if (($sml == 0) || ($a < $sml)) {
            return $a;
        } else {
            return $sml;
        }
    }, $terraB);

    if ($sml >= $terraB) {
        $sml = 1;
    }

    $base = pow(10,floor(round(log10($sml/pow(1024,$magnitude)),3)));
    $baseByte = $base * pow(1024, $magnitude);
    // if really close to smallest value use half so you can see the bar

    // google chart sometimes refuses to draw y axis units when base is 5*10^x

    //if ($sml / $baseByte < 1.2) {
    //    $base = $base / 2;
    // if smallest val is more than 6 times, use 5 times so smallest bar is not so big
    //} else if ($sml / $baseByte > 6 ) {
    //    $base = 5 * $base;
    //}

    if ($sml / $baseByte < 1.05) {
        $base = $base / 10;
    }

    return $base;
}

function getLargestPrefix($magnitude)
{
    $units = ['B', 'K', 'M', 'G', 'T'];

    return $units[$magnitude];
}

function getVnstatData($path, $type, $interface)
{
    global $version;
    global $smarty;

    $vnstat_information = []; // Create an empty array for use later

    $vnstatJSON = popen("$path --json -i $interface", "r");
    $vnstatDecoded = "";

    if (is_resource($vnstatJSON)) {
        $iBuffer = '';
        while (!feof($vnstatJSON)) {
            $iBuffer .= fgets($vnstatJSON);
        }

        $vnstatDecoded = $iBuffer;

        pclose($vnstatJSON);
    }

    $vnstatDecoded = json_decode($vnstatDecoded, true);

    $fiveGraph = [];
    $five = [];
    $hourlyGraph = [];
    $hourly = [];
    $dailyGraph = [];
    $daily = [];
    $monthlyGraph = [];
    $monthly = [];
    $top10 = [];

    if( isset( $vnstatDecoded['jsonversion'] )){
        $version = $vnstatDecoded['jsonversion'];
    }

    $smarty->assign('version', $version);

    $s='';
    if( $version == 1 ){
        $s = 's';
    }

    $i = 0;
    foreach ($vnstatDecoded['interfaces'][0]['traffic']['top'.$s] as $top) {
        if (is_array($top)) {
            ++$i;

            $top10[$i]['label'] = date('d/m/Y', strtotime($top['date']['month'] . "/" . $top['date']['day'] . "/" . $top['date']['year']));
            $top10[$i]['rx'] = bytesToString($top['rx']);
            $top10[$i]['tx'] = bytesToString($top['tx']);
            $top10[$i]['totalraw'] = ($top['rx'] + $top['tx']);
            $top10[$i]['total'] = bytesToString($top['rx'] + $top['tx']);
        }
    }

    if( $version > 1 ){
    $i = 0;
    foreach ($vnstatDecoded['interfaces'][0]['traffic']['fiveminute'] as $min) {
        if (is_array($min)) {
            ++$i;

            $five[$i]['label'] = date('m/d G:i', mktime($min['time']['hour'], $min['time']['minute'], 0, $min['date']['month'], $min['date']['day'], $min['date']['year']));
            $five[$i]['rx'] = bytesToString($min['rx']);
            $five[$i]['tx'] = bytesToString($min['tx']);
            $five[$i]['totalraw'] = ($min['rx'] + $min['tx']);
            $five[$i]['total'] = bytesToString($min['rx'] + $min['tx']);
            $five[$i]['time'] = mktime($min['time']['hour'], $min['time']['minute'], 0, $min['date']['month'], $min['date']['day'], $min['date']['year']);

            $fiveGraph[$i]['label'] = sprintf("Date(%d, %d, %d, %d, %d)",$min['date']['year'],$min['date']['month']-1,$min['date']['day'],$min['time']['hour'],$min['time']['minute']);
            $fiveGraph[$i]['rx'] = $min['rx'];
            $fiveGraph[$i]['tx'] = $min['tx'];
            $fiveGraph[$i]['total'] = ($min['rx'] + $min['tx']);
            $fiveGraph[$i]['time'] = mktime($min['time']['hour'], $min['time']['minute'], 0, $min['date']['month'], $min['date']['day'], $min['date']['year']);
        }
    }
    }

    $i = 0;
    foreach ($vnstatDecoded['interfaces'][0]['traffic']['day'.$s] as $day) {
        if (is_array($day)) {
            ++$i;

            $daily[$i]['label'] = date('d/m/Y', mktime(0, 0, 0, $day['date']['month'], $day['date']['day'], $day['date']['year']));
            $daily[$i]['rx'] = bytesToString($day['rx']);
            $daily[$i]['tx'] = bytesToString($day['tx']);
            $daily[$i]['totalraw'] = ($day['rx'] + $day['tx']);
            $daily[$i]['total'] = bytesToString($day['rx'] + $day['tx']);
            $daily[$i]['time'] = mktime(0, 0, 0, $day['date']['month'], $day['date']['day'], $day['date']['year']);

            $dailyGraph[$i]['label'] = sprintf("Date(%d, %d, %d, %d, %d)",$day['date']['year'],$day['date']['month']-1,$day['date']['day'],0,0);
            $dailyGraph[$i]['rx'] = $day['rx'];
            $dailyGraph[$i]['tx'] = $day['tx'];
            $dailyGraph[$i]['total'] = ($day['rx'] + $day['tx']);
            $dailyGraph[$i]['time'] = mktime(0, 0, 0, $day['date']['month'], $day['date']['day'], $day['date']['year']);
        }
    }

    $i = 0;
    foreach ($vnstatDecoded['interfaces'][0]['traffic']['hour'.$s] as $hour) {
        if (is_array($hour)) {
            ++$i;

            if( $version == 1 ){
                $h = $hour['id'];
            } else {
                $h = $hour['time']['hour'];
            }

            $hourly[$i]['label'] = date('m/d G:i', mktime($h, 0, 0, $hour['date']['month'], $hour['date']['day'], $hour['date']['year']));
            $hourly[$i]['rx'] = bytesToString($hour['rx']);
            $hourly[$i]['tx'] = bytesToString($hour['tx']);
            $hourly[$i]['totalraw'] = ($hour['rx'] + $hour['tx']);
            $hourly[$i]['total'] = bytesToString($hour['rx'] + $hour['tx']);
            $hourly[$i]['time'] = mktime($h, 0, 0, $hour['date']['month'], $hour['date']['day'], $hour['date']['year']);

            $hourlyGraph[$i]['label'] = sprintf("Date(%d, %d, %d, %d, %d)",$hour['date']['year'],$hour['date']['month']-1,$hour['date']['day'],$h,0);
            $hourlyGraph[$i]['rx'] = $hour['rx'];
            $hourlyGraph[$i]['tx'] = $hour['tx'];
            $hourlyGraph[$i]['total'] = ($hour['rx'] + $hour['tx']);
            $hourlyGraph[$i]['time'] = mktime($h, 0, 0, $hour['date']['month'], $hour['date']['day'], $hour['date']['year']);
        }
    }

    asort($vnstatDecoded['interfaces'][0]['traffic']['month'.$s]);

    $i = 0;
    foreach ($vnstatDecoded['interfaces'][0]['traffic']['month'.$s] as $month) {
        if (is_array($month)) {
            ++$i;

            $monthly[$i]['label'] = date('F', mktime(0, 0, 0, $month['date']['month'], 10));
            $monthly[$i]['rx'] = bytesToString($month['rx']);
            $monthly[$i]['tx'] = bytesToString($month['tx']);
            $monthly[$i]['totalraw'] = ($month['rx'] + $month['tx']);
            $monthly[$i]['total'] = bytesToString($month['rx'] + $month['tx']);
            $monthly[$i]['time'] = mktime(0, 0, 0, $month['date']['month'], 1, $month['date']['year']);

            $monthlyGraph[$i]['label'] = date('F', mktime(0, 0, 0, $month['date']['month'], 10));
            $monthlyGraph[$i]['rx'] = $month['rx'];
            $monthlyGraph[$i]['tx'] = $month['tx'];
            $monthlyGraph[$i]['total'] = ($month['rx'] + $month['tx']);
            $monthlyGraph[$i]['time'] = mktime(0, 0, 0, $month['date']['month'], 1, $month['date']['year']);
        }
    }

    $sorting_function = function ($item1, $item2) {
        if ($item1['time'] == $item2['time']) {
            return 0;
        } else {
            return $item1['time'] > $item2['time'] ? -1 : 1;
        }
    };

    usort($five, $sorting_function);
    usort($fiveGraph, $sorting_function);
    usort($hourly, $sorting_function);
    usort($hourlyGraph, $sorting_function);
    usort($daily, $sorting_function);
    usort($dailyGraph, $sorting_function);
    usort($monthly, $sorting_function);
    usort($monthlyGraph, $sorting_function);

    // Sort Top 10 Days by Highest Total Usage first
    usort($top10, function ($item1, $item2) {
        if ($item1['totalraw'] == $item2['totalraw']) {
            return 0;
        } else {
            return $item1['totalraw'] > $item2['totalraw'] ? -1 : 1;
        }
    });

    switch ($type) {
        case "fiveGraph":
            return $fiveGraph;
        case "five":
            return $five;
        case "hourlyGraph":
            return $hourlyGraph;
        case "hourly":
            return $hourly;
        case "dailyGraph":
            return $dailyGraph;
        case "daily":
            return $daily;
        case "monthlyGraph":
            return array_slice($monthlyGraph, 0, 12, true);
        case "monthly":
            return $monthly;
        case "top10":
            return $top10;
    }

    return false;
}
