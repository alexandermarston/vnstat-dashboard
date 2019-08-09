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

// Set the default system Timezone
date_default_timezone_set('Europe/London');

// Path of vnstat
$vnstat_bin_dir = '/usr/bin/vnstat';

// Uncomment to override default config file
//$vnstat_config = '/etc/vnstat.conf';

// Set to true to set your own interfaces
$use_predefined_interfaces = false;

if ($use_predefined_interfaces == true) {
    $interface_list = ["eth0", "eth1"];

    $interface_name['eth0'] = "Internal #1";
    $interface_name['eth1'] = "Internal #2";
} else {
    $interface_list = getVnstatInterfaces($vnstat_bin_dir);

    foreach ($interface_list as $interface) {
        $interface_name[$interface] = $interface;
    }
}
