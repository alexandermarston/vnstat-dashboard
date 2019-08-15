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

// Get the largest value in an array
function getLargestValue($array) {
    return $max = array_reduce($array, function ($a, $b) {
        return $a > $b['total'] ? $a : $b['total'];
    });
}

function formatSize($bytes, $vnstatJsonVersion) {
    // json version 1 = convert from KiB
    // json version 2 = convert from bytes
    if ($vnstatJsonVersion == 1) {
        $bytes *= 1024;  // convert from kibibytes to bytes
    }

    return formatBytes($bytes);
}

function formatBytes($bytes, $decimals = 2) {
    $base = log(floatval($bytes), 1024);
    $suffixes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    return round(pow(1024, $base - floor($base)), $decimals) .' '. $suffixes[floor($base)];
}

function formatBytesTo($bytes, $delimiter, $decimals = 2) {
    if ($bytes == 0) {
        return '0';
    }

    $k = 1024;
    $dm = $decimals < 0 ? 0 : $decimals;
    $sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    $i = array_search($delimiter, $sizes);

    return number_format(($bytes / pow($k, $i)), $decimals);
}

function kibibytesToBytes($kibibytes, $vnstatJsonVersion) {
    if ($vnstatJsonVersion == 1) {
        return $kibibytes *= 1024;
    } else {
        return $kibibytes;
    }
}

function getLargestPrefix($kb)
{
    $units = ['TB', 'GB', 'MB', 'KB', 'B'];
    $scale = 1024 * 1024 * 1024 * 1024;
    $ui = 0;

    while ((($kb < $scale) && ($scale > 1))) {
        $ui++;
        $scale = $scale / 1024;
    }

    return $units[$ui];
}

function sortingFunction($item1, $item2) {
    if ($item1['time'] == $item2['time']) {
        return 0;
    } else {
        return $item1['time'] > $item2['time'] ? -1 : 1;
    }
};

?>
