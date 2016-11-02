<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function kbytes_to_string($kb, $wSuf) {
    $byte_notation = "MB";

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

    if (isset($wSuf)) {
        return sprintf("%0.2f %s", ($kb/$scale),$units[$ui]);
    } else {
        return sprintf("%0.2f", ($kb / $scale));
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
    
        $vnstat_temp = trim(str_replace("Available interfaces: ", "", $iBuffer));
        
        $vnstat_interfaces = explode(" ", $vnstat_temp);
        pclose($vnstatIF);
    }
    
    return $vnstat_interfaces;
}

function get_vnstat_data($path, $type, $interface) {

    $vnstat_information = array(); // Create an empty array for use later

    $vnstatDS = popen("$path --dumpdb -i $interface", "r");
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

    $hourly = array();
    $daily = array();
    $monthly = array();
    $top10 = array();

    foreach ($vnstat_information as $vnstat_line) {
        $data = explode(";", trim($vnstat_line));
        switch ($data[0]) {
            case "h": // Hourly
                $hourly[$data[1]]['time'] = $data[2];
                $hourly[$data[1]]['label'] = date("ga", ($data[2] - ($data[2] % 3600)));
                $hourly[$data[1]]['rx'] = kbytes_to_string($data[3]);
                $hourly[$data[1]]['tx'] = kbytes_to_string($data[4]);
                $hourly[$data[1]]['total'] = kbytes_to_string($data[3] + $data[4]);
                $hourly[$data[1]]['act'] = 1;
                break;
            case "d": // Daily
                $daily[$data[1]]['time'] = $data[2];
                $daily[$data[1]]['label'] = date("d/m/Y", $data[2]);
                $daily[$data[1]]['rx'] = kbytes_to_string($data[3] * 1024 + $data[5], true);
                $daily[$data[1]]['tx'] = kbytes_to_string($data[4] * 1024 + $data[6], true);
                $daily[$data[1]]['total'] = kbytes_to_string(($data[3] * 1024 + $data[5]) + ($data[4] * 1024 + $data[6]), true);
                $daily[$data[1]]['act'] = $data[7];
                break;
            case "m": // Monthly
                $monthly[$data[1]]['time'] = $data[2];
                $monthly[$data[1]]['label'] = date("F", $data[2]);
                $monthly[$data[1]]['rx'] = kbytes_to_string($data[3] * 1024 + $data[5], true);
                $monthly[$data[1]]['tx'] = kbytes_to_string($data[4] * 1024 + $data[6], true);
                $monthly[$data[1]]['total'] = kbytes_to_string(($data[3] * 1024 + $data[5]) + ($data[4] * 1024 + $data[6]), true);
                $monthly[$data[1]]['act'] = $data[7];
                break;
            case "t": // Top 10
                $top10[$data[1]]['time'] = $data[2];
                $top10[$data[1]]['label'] = date("d/m/Y", $data[2]);
                $top10[$data[1]]['rx'] = kbytes_to_string($data[3] * 1024 + $data[5], true);
                $top10[$data[1]]['tx'] = kbytes_to_string($data[4] * 1024 + $data[6], true);
                $top10[$data[1]]['totalraw'] = (($data[3] * 1024 + $data[5]) + ($data[4] * 1024 + $data[6]));
                $top10[$data[1]]['total'] = kbytes_to_string(($data[3] * 1024 + $data[5]) + ($data[4] * 1024 + $data[6]), true);
                $top10[$data[1]]['act'] = $data[7];
                break;
        }
    }

    usort($hourly, function ($item1, $item2) {
        if ($item1['time'] == $item2['time']) return 0;
        return $item1['time'] < $item2['time'] ? -1 : 1;
    });
    
    usort($daily, function ($item1, $item2) {
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
        case "hourly":
            return $hourly;
        case "daily":
            return $daily;
        case "monthly":
            return $monthly;
        case "top10":
            return $top10;
    }
}
