<?php

error_reporting(E_ALL | E_NOTICE);

// Path of vnstat
$vnstat_bin_dir = '/usr/local/bin/vnstat';

// Set to true to set your own interfaces
$use_predefined_interfaces = false;

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
