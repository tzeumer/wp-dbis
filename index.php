<?php
require 'inc/dbis/class.CloneDBIS.php';

header('Content-Type: text/html; charset=utf-8');

set_time_limit(10000);
$time_start = script_timer();
$wp_dbis = new CloneDBIS();
//$wp_dbis->dbis_id = 'ubw_hh';
$wp_dbis->start_dbis();
echo "Time: ".script_timer($time_start);

/** /
 * 
 * @param type $start
 * @return type
 */
function script_timer($start = NULL) {
    $mtime = microtime(); $mtime = explode(' ', $mtime); $mtime = $mtime[1] + $mtime[0];
    if (!$start) {
      return $mtime;
    }
    $endtime = $mtime;
    $totaltime = ($endtime - $start);
    return $totaltime;
}
?>