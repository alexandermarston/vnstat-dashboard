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
function kbytes_to_string($kb, $wSuf = false, $byte_notation = null) {
    $units = array('TB', 'GB', 'MB', 'KB');
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

function get_vnstat_interfaces($path) {
    $vnstat_interfaces = array(); // Create an empty array

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

function get_largest_value($array) {
    return $max = array_reduce($array, function($a, $b) {
        return $a > $b['total'] ? $a : $b['total'];
    });
}

function get_largest_prefix($kb) {
    $units = array('TB', 'GB', 'MB', 'KB');
    $scale = 1024 * 1024 * 1024;
    $ui = 0;

    while ((($kb < $scale) && ($scale > 1))) {
        $ui++;
        $scale = $scale / 1024;
    }

    return $units[$ui];
}

function get_vnstat_data($path, $type, $interface) {
    global $byte_formatter, $vnstat_config_format_hour;

    $vnstat_information = array(); // Create an empty array for use later

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

    $hourlyGraph = array();
    $hourly = array();
    $dailyGraph = array();
    $daily = array();
    $monthlyGraph = array();
    $monthly = array();
    $top10 = array();

    foreach ($vnstatDecoded['interfaces'][0]['traffic']['tops'] as $top) {
        if (is_array($top)) {
            ++$i;

            $top10[$i]['label'] = date('d/m/Y', strtotime($top['date']['month'] . "/" . $top['date']['day'] . "/" . $top['date']['year']));
            $top10[$i]['rx'] = kbytes_to_string($top['rx']);
            $top10[$i]['tx'] = kbytes_to_string($top['tx']);
            $top10[$i]['totalraw'] = ($top['rx'] + $top['tx']);
            $top10[$i]['total'] = kbytes_to_string($top['rx'] + $top['tx']);
        }
    }

    foreach ($vnstatDecoded['interfaces'][0]['traffic']['days'] as $day) {
        if (is_array($day)) {
            ++$i;

            $daily[$i]['label'] = date('d/m/Y', mktime(0, 0, 0, $day['date']['month'], $day['date']['day'], $day['date']['year']));
            $daily[$i]['rx'] = kbytes_to_string($day['rx']);
            $daily[$i]['tx'] = kbytes_to_string($day['tx']);
            $daily[$i]['totalraw'] = ($day['rx'] + $day['tx']);
            $daily[$i]['total'] = kbytes_to_string($day['rx'] + $day['tx']);
            $daily[$i]['time'] = mktime(0, 0, 0, $day['date']['month'], $day['date']['day'], $day['date']['year']);

            $dailyGraph[$i]['label'] = date('jS', mktime(0, 0, 0, $day['date']['month'], $day['date']['day'], $day['date']['year']));
            $dailyGraph[$i]['rx'] = $day['rx'];
            $dailyGraph[$i]['tx'] = $day['tx'];
            $dailyGraph[$i]['total'] = ($day['rx'] + $day['tx']);
            $dailyGraph[$i]['time'] = mktime(0, 0, 0, $day['date']['month'], $day['date']['day'], $day['date']['year']);
        }
    }

    foreach ($vnstatDecoded['interfaces'][0]['traffic']['hours'] as $hour) {
        if (is_array($hour)) {
            ++$i;

            $hourly[$i]['label'] = date("ga", mktime($hour['id'], 0, 0, $hour['date']['month'], $hour['date']['day'], $hour['date']['year']));
            $hourly[$i]['rx'] = kbytes_to_string($hour['rx']);
            $hourly[$i]['tx'] = kbytes_to_string($hour['tx']);
            $hourly[$i]['totalraw'] = ($hour['rx'] + $hour['tx']);
            $hourly[$i]['total'] = kbytes_to_string($hour['rx'] + $hour['tx']);
            $hourly[$i]['time'] = mktime($hour['id'], 0, 0, $hour['date']['month'], $hour['date']['day'], $hour['date']['year']);

            $hourlyGraph[$i]['label'] = date("ga", mktime($hour['id'], 0, 0, $hour['date']['month'], $hour['date']['day'], $hour['date']['year']));
            $hourlyGraph[$i]['rx'] = $hour['rx'];
            $hourlyGraph[$i]['tx'] = $hour['tx'];
            $hourlyGraph[$i]['total'] = ($hour['rx'] + $hour['tx']);
            $hourlyGraph[$i]['time'] = mktime($hour['id'], 0, 0, $hour['date']['month'], $hour['date']['day'], $hour['date']['year']);
        }
    }

    foreach ($vnstatDecoded['interfaces'][0]['traffic']['months'] as $month) {
        if (is_array($month)) {
            ++$i;

            $monthly[$i]['label'] = date('F', mktime(0, 0, 0, $month['date']['month'], 10));
            $monthly[$i]['rx'] = kbytes_to_string($month['rx']);
            $monthly[$i]['tx'] = kbytes_to_string($month['tx']);
            $monthly[$i]['totalraw'] = ($month['rx'] + $month['tx']);
            $monthly[$i]['total'] = kbytes_to_string($month['rx'] + $month['tx']);
            $monthly[$i]['time'] = mktime(0, 0, 0, $hour['date']['month'], 1, $hour['date']['year']);

            $monthlyGraph[$i]['label'] = date('F', mktime(0, 0, 0, $month['date']['month'], 10));
            $monthlyGraph[$i]['rx'] = $month['rx'];
            $monthlyGraph[$i]['tx'] = $month['tx'];
            $monthlyGraph[$i]['total'] = ($month['rx'] + $month['tx']);
            $monthlyGraph[$i]['time'] = mktime(0, 0, 0, $hour['date']['month'], 1, $hour['date']['year']);
        }
    }

    $time_sort_callback=function ($item1, $item2){
        return $item1['time'] <=> $item2['time'];
    };

    usort($hourlyGraph, $time_sort_callback);
    usort($hourly, $time_sort_callback);
    usort($dailyGraph, $time_sort_callback);
    usort($daily, $time_sort_callback);
    usort($monthlyGraph, $time_sort_callback);
    usort($monthly, $time_sort_callback);

    // Sort Top 10 Days by Highest Total Usage first
    usort($top10, function ($item1, $item2) {
        return $item1['totalraw'] <=> $item2['totalraw'];
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
}
