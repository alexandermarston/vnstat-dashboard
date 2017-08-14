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

error_reporting(E_ALL | E_NOTICE);

// date format: hourly
// ga = 12-hour am/pm (ex. 11am)
// H = 24-hour (ex. 23)
$vnstat_config_format_hour = "ga";

// Path of vnstat
$vnstat_bin_dir = '/usr/local/bin/vnstat';

// Set to true to set your own interfaces
$use_predefined_interfaces = false;

// Byte format to use in graphs
$byte_formatter = "MB";

if ($use_predefined_interfaces == true) {
    $interface_list = array("eth0", "eth1");

    $interface_name['eth0'] = "Internal #1";
    $interface_name['eth1'] = "Internal #2";
} else {
    $interface_list = get_vnstat_interfaces($vnstat_bin_dir);
    
    foreach ($interface_list as $interface)
    {
        $interface_name[$interface] = $interface;
    }
}
?>
