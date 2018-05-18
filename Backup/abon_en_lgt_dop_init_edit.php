<?php

set_time_limit(6000); 

//header('Content-type: text/html; charset=utf-8');
header("Content-type: application/json;charset=utf-8");

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(1);

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

//sleep(10);
$mmgg = sql_field_val('mmgg', 'date'); 


$SQL = "select acm_dop_lgt_fill_fun($mmgg)::int as result; ";

$result = pg_query($Link, $SQL);
if ($result) {
    
    $row = pg_fetch_array($result);     
    echo_result(-1,'Records created',$row['result']);

} else {
    echo_result(2, pg_last_error($Link));

}


?>
