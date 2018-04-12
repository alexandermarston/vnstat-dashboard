<?php         

    $int=$_GET['i']; 
     
    $rx[] = @file_get_contents("/sys/class/net/$int/statistics/rx_bytes"); 
    $tx[] = @file_get_contents("/sys/class/net/$int/statistics/tx_bytes"); 
    sleep(1); 
    $rx[] = @file_get_contents("/sys/class/net/$int/statistics/rx_bytes"); 
    $tx[] = @file_get_contents("/sys/class/net/$int/statistics/tx_bytes"); 
     
    $tbps = $tx[1] - $tx[0]; 
    $rbps = $rx[1] - $rx[0]; 
     
    $round_rx=round($rbps/1024, 2); 
    $round_tx=round($tbps/1024, 2); 
    $data['rx'] = $round_rx; 
    $data['tx'] = $round_tx; 

    echo json_encode($data, JSON_FORCE_OBJECT); 

?>