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
        return $a > $b['totalUnformatted'] ? $a : $b['totalUnformatted'];
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

    $vnstatDS = popen("$path --dumpdb -i $interface", "r");
    //$vnstatDS = fopen("dump.db", "r");
    if (is_resource($vnstatDS)) {
        $buffer = '';
        while (!feof($vnstatDS)) {
            $buffer .= fgets($vnstatDS);
        }
        $vnstat_information = explode("\n", $buffer);
        pclose($vnstatDS);
    }

    if (isset($vnstat_information[0]) && strpos($vnstat_information[0], 'Error') !== false) {
        return;
    }

    $hourlyGraph = array();
    $hourly = array();
    $dailyGraph = array();
    $daily = array();
    $monthlyGraph = array();
    $monthly = array();
    $top10 = array();

    foreach ($vnstat_information as $vnstat_line) {
        $data = explode(";", trim($vnstat_line));
        switch ($data[0]) {
            case "h": // Hourly
                // Set-up the hourly graph data
                $hourlyGraph[$data[1]]['time'] = $data[2];
                $hourlyGraph[$data[1]]['label'] = date($vnstat_config_format_hour, ($data[2] - ($data[2] % 3600)));
                $hourlyGraph[$data[1]]['rx'] = kbytes_to_string($data[3], true, $byte_formatter);
                $hourlyGraph[$data[1]]['tx'] = kbytes_to_string($data[4], true, $byte_formatter);
                $hourlyGraph[$data[1]]['total'] = kbytes_to_string($data[3] + $data[4], true, $byte_formatter);
                $hourlyGraph[$data[1]]['totalUnformatted'] = ($data[3] + $data[4]);
                $hourlyGraph[$data[1]]['act'] = 1;

                // Set up the hourly table data
                $hourly[$data[1]]['time'] = $data[2];
                $hourly[$data[1]]['label'] = date($vnstat_config_format_hour, ($data[2] - ($data[2] % 3600)));
                $hourly[$data[1]]['rx'] = kbytes_to_string($data[3]);
                $hourly[$data[1]]['tx'] = kbytes_to_string($data[4]);
                $hourly[$data[1]]['total'] = kbytes_to_string($data[3] + $data[4]);
                $hourly[$data[1]]['act'] = 1;
                break;
            case "d": // Daily
                // Set-up the daily graph data
                $dailyGraph[$data[1]]['time'] = $data[2];
                $dailyGraph[$data[1]]['label'] = date("jS", $data[2]);
                $dailyGraph[$data[1]]['rx'] = kbytes_to_string(($data[3] * 1024 + $data[5]), true, $byte_formatter);
                $dailyGraph[$data[1]]['tx'] = kbytes_to_string(($data[4] * 1024 + $data[6]), true, $byte_formatter);
                $dailyGraph[$data[1]]['total'] = kbytes_to_string(($data[3] * 1024 + $data[5]) + ($data[4] * 1024 + $data[6]), true, $byte_formatter);
                $dailyGraph[$data[1]]['totalUnformatted'] = (($data[3] * 1024 + $data[5]) + ($data[4] * 1024 + $data[6]));
                $dailyGraph[$data[1]]['act'] = 1;
                
                $daily[$data[1]]['time'] = $data[2];
                $daily[$data[1]]['label'] = date("d/m/Y", $data[2]);
                $daily[$data[1]]['rx'] = kbytes_to_string($data[3] * 1024 + $data[5]);
                $daily[$data[1]]['tx'] = kbytes_to_string($data[4] * 1024 + $data[6]);
                $daily[$data[1]]['total'] = kbytes_to_string(($data[3] * 1024 + $data[5]) + ($data[4] * 1024 + $data[6]));
                $daily[$data[1]]['act'] = $data[7];
                break;
            case "m": // Monthly
                // Set-up the monthly graph data
                $monthlyGraph[$data[1]]['time'] = $data[2];
                $monthlyGraph[$data[1]]['label'] = date("F", ($data[2] - ($data[2] % 3600)));
                $monthlyGraph[$data[1]]['rx'] = kbytes_to_string(($data[3] * 1024 + $data[5]), true, $byte_formatter);
                $monthlyGraph[$data[1]]['tx'] = kbytes_to_string(($data[4] * 1024 + $data[6]), true, $byte_formatter);
                $monthlyGraph[$data[1]]['total'] = kbytes_to_string((($data[3] * 1024 + $data[5]) + ($data[4] * 1024 + $data[6])), true, $byte_formatter);
                $monthlyGraph[$data[1]]['totalUnformatted'] = ($data[3] + $data[4]);
                $monthlyGraph[$data[1]]['act'] = 1;
                
                $monthly[$data[1]]['time'] = $data[2];
                $monthly[$data[1]]['label'] = date("F", $data[2]);
                $monthly[$data[1]]['rx'] = kbytes_to_string($data[3] * 1024 + $data[5]);
                $monthly[$data[1]]['tx'] = kbytes_to_string($data[4] * 1024 + $data[6]);
                $monthly[$data[1]]['total'] = kbytes_to_string(($data[3] * 1024 + $data[5]) + ($data[4] * 1024 + $data[6]));
                $monthly[$data[1]]['act'] = $data[7];
                break;
            case "t": // Top 10
                $top10[$data[1]]['time'] = $data[2];
                $top10[$data[1]]['label'] = date("d/m/Y", $data[2]);
                $top10[$data[1]]['rx'] = kbytes_to_string($data[3] * 1024 + $data[5]);
                $top10[$data[1]]['tx'] = kbytes_to_string($data[4] * 1024 + $data[6]);
                $top10[$data[1]]['totalraw'] = (($data[3] * 1024 + $data[5]) + ($data[4] * 1024 + $data[6]));
                $top10[$data[1]]['total'] = kbytes_to_string(($data[3] * 1024 + $data[5]) + ($data[4] * 1024 + $data[6]));
                $top10[$data[1]]['act'] = $data[7];
                break;
        }
    }

    usort($hourlyGraph, function ($item1, $item2) {
        if ($item1['time'] == $item2['time']) return 0;
        return $item1['time'] < $item2['time'] ? -1 : 1;
    });

    usort($hourly, function ($item1, $item2) {
        if ($item1['time'] == $item2['time']) return 0;
        return $item1['time'] < $item2['time'] ? -1 : 1;
    });
    
    usort($dailyGraph, function ($item1, $item2) {
        if ($item1['time'] == $item2['time']) return 0;
        return $item1['time'] > $item2['time'] ? -1 : 1;
    });

    usort($daily, function ($item1, $item2) {
        if ($item1['time'] == $item2['time']) return 0;
        return $item1['time'] > $item2['time'] ? -1 : 1;
    });
    
    usort($monthlyGraph, function ($item1, $item2) {
        if ($item1['time'] == $item2['time']) return 0;
        return $item1['time'] > $item2['time'] ? -1 : 1;
    });

    usort($monthly, function ($item1, $item2) {
        if ($item1['time'] == $item2['time']) return 0;
        return $item1['time'] > $item2['time'] ? -1 : 1;
    });

    // Sort Top 10 Days by Highest Total Usage first
    usort($top10, function ($item1, $item2) {
        if ($item1['totalraw'] == $item2['totalraw']) return 0;
        return $item1['totalraw'] > $item2['totalraw'] ? -1 : 1;
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
?>
