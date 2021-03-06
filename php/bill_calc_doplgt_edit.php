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

$result = pg_query($Link, "insert into syi_sysvars_tmp (id, ident,type_ident, value_ident) 
     values(120,'id_person','int', $session_user);");

if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    pg_query($Link, "rollback");
    return;
}

 $result = pg_query($Link, "update syi_sysvars_tmp set value_ident=1 where id=50;");
 if (!($result)) {
     echo_result(2, pg_last_error($Link) );
     pg_query($Link, "rollback");
     return;
 }

$SQL = "select calc_bill(id_paccnt,mmgg) from (select distinct mmgg, id_paccnt from acm_dop_lgt_tbl where mmgg = fun_mmgg()) as ss ; ";
$result = pg_query($Link, $SQL);
 if (!($result)) {
     echo_result(2, pg_last_error($Link) );
     pg_query($Link, "rollback");
     return;
 }

 $result = pg_query($Link, "update syi_sysvars_tmp set value_ident=0 where id=50;");
 if (!($result)) {
     echo_result(2, pg_last_error($Link) );
     pg_query($Link, "rollback");
     return;
 }


$result = pg_query($Link, "select calc_saldo();");
 if (!($result)) {
     echo_result(2, pg_last_error($Link) );
     pg_query($Link, "rollback");
     return;
 }
 
 $result = pg_query($Link, "select all_repayment();"); 
 if ($result) {
    
    $row = pg_fetch_array($result);     
    echo_result(-1,'Bill created',1);
    pg_query($Link,"commit");   

 } else {
    echo_result(2, pg_last_error($Link));
    pg_query($Link, "rollback");
 }


?>
