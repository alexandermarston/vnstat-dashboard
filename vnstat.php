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

// $wSuf (without suffix MB, GB, etc)
function kbytesToString($kb, $wSuf = false, $byte_notation = null)
{
    $units = ['TB', 'GB', 'MB', 'KB'];
    $scale = 1024 * 1024 * 1024;
    $ui = 0;

    $custom_size = isset($byte_notation) && in_array($byte_notation, $units);

    while ((($kb < $scale) && ($scale > 1)) || $custom_size) {
        $ui++;
        $scale = $scale / 1024;

        if ($custom_size && $units[$ui] == $byte_notation) {
            break;
        }
    }

    if ($wSuf == true) {
        return sprintf("%0.2f", ($kb / $scale));
    } else {
        return sprintf("%0.2f %s", ($kb / $scale), $units[$ui]);
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

function getLargestPrefix($kb)
{
    $units = ['TB', 'GB', 'MB', 'KB'];
    $scale = 1024 * 1024 * 1024;
    $ui = 0;

    while ((($kb < $scale) && ($scale > 1))) {
        $ui++;
        $scale = $scale / 1024;
    }

    return $units[$ui];
}

function getVnstatData($path, $type, $interface)
{
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

    $hourlyGraph = [];
    $hourly = [];
    $dailyGraph = [];
    $daily = [];
    $monthlyGraph = [];
    $monthly = [];
    $top10 = [];
    $top10Graph = [];

    generateGraph(
        $vnstatDecoded['interfaces'][0]['traffic']['tops'],
        ['type' => 'd/m/Y'],
        $top,
        $top10Graph
    );
    unset($top10Graph);

    generateGraph(
        $vnstatDecoded['interfaces'][0]['traffic']['days'],
        ['type' => 'd/m/Y', 'typeGraph' => 'jS'],
        $daily,
        $dailyGraph
    );

    generateGraph(
        $vnstatDecoded['interfaces'][0]['traffic']['hours'],
        ['type' => 'ga', 'typeGraph' => 'ga'],
        $hourly,
        $hourlyGraph,
        true
    );

    generateGraph(
        $vnstatDecoded['interfaces'][0]['traffic']['months'],
        ['type' => 'F', 'typeGraph' => 'F'],
        $monthly,
        $monthlyGraph
    );

    $sorting_function = function ($item1, $item2) {
        if ($item1['time'] == $item2['time']) {
            return 0;
        } else {
            return $item1['time'] > $item2['time'] ? -1 : 1;
        }
    };

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
        case "hourlyGraph":
            return $hourlyGraph;
        case "hourly":
            return $hourly;
        case "dailyGraph":
            return $dailyGraph;
        case "daily":
            return $daily;
        case "monthlyGraph":
            return $monthlyGraph;
        case "monthly":
            return $monthly;
        case "top10":
            return $top10;
    }

    return false;
}

function generateGraph($traffic, $dateFormat, &$type, &$typeGraph, $hour = false)
{
    $i = 0;
    foreach ($traffic as $kind) {
        if (is_array($kind)) {
            ++$i;
            $startTime = 0;
            if($hour) {
                $startTime = $kind['id'];
            }

            $type[$i]['label'] = date($dateFormat['type'], mktime($startTime, 0, 0, $kind['date']['month'], $kind['date']['day'], $kind['date']['year']));
            $type[$i]['rx'] = kbytesToString($kind['rx']);
            $type[$i]['tx'] = kbytesToString($kind['tx']);
            $type[$i]['totalraw'] = ($kind['rx'] + $kind['tx']);
            $type[$i]['total'] = kbytesToString($kind['rx'] + $kind['tx']);

            if (is_array($typeGraph)) {
                $type[$i]['time'] = mktime($startTime, 0, 0, $kind['date']['month'], $kind['date']['day'], $kind['date']['year']);

                $typeGraph[$i]['label'] = date($dateFormat['typeGraph'], mktime($startTime, 0, 0, $kind['date']['month'], $kind['date']['day'], $kind['date']['year']));
                $typeGraph[$i]['rx'] = $kind['rx'];
                $typeGraph[$i]['tx'] = $kind['tx'];
                $typeGraph[$i]['total'] = ($kind['rx'] + $kind['tx']);
                $typeGraph[$i]['time'] = mktime($startTime, 0, 0, $kind['date']['month'], $kind['date']['day'], $kind['date']['year']);
            }
        }
    }
}