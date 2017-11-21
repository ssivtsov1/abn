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
$id_paccnt = sql_field_val('id_paccnt', 'int'); 
$mmgg = sql_field_val('mmgg', 'date'); 

$result = pg_query($Link, "begin");
if (!($result)) {
    echo_result(2, pg_last_error($Link));
    return;
}

$result = pg_query($Link, "select crt_ttbl();");
if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    pg_query($Link, "rollback");
    return;
}

$SQL = "select calc_saldo($mmgg,10,$id_paccnt)::int as result; "; 

$result = pg_query($Link, $SQL);
if ($result) {
    
    $row = pg_fetch_array($result);     
    echo_result(-1,'Bill created',$row['result']);
    pg_query($Link,"commit");   

} else {
    echo_result(2, pg_last_error($Link));
    pg_query($Link, "rollback");
}

?>
