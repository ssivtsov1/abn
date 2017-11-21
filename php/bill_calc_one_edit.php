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
$mmgg = sql_field_val('mmgg', 'mmgg'); 

$Query=" select to_char(fun_mmgg(), 'YYYY-MM-DD')::varchar as mmgg;";
$result = pg_query($Link,$Query) ;
$row = pg_fetch_array($result);
$curr_mmgg = "'".$row['mmgg']."'";

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

if ($curr_mmgg!=$mmgg)
{
 $result = pg_query($Link, "update syi_sysvars_tmp set value_ident=1 where id=50;");
 if (!($result)) {
     echo_result(2, pg_last_error($Link) );
     pg_query($Link, "rollback");
     return;
 }
}

$SQL = "select calc_bill($id_paccnt,$mmgg)::int as result; ";
$result = pg_query($Link, $SQL);
if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    pg_query($Link, "rollback");
    return;
}

if ($curr_mmgg!=$mmgg)
{
 $result = pg_query($Link, "update syi_sysvars_tmp set value_ident=0 where id=50;");
 if (!($result)) {
     echo_result(2, pg_last_error($Link) );
     pg_query($Link, "rollback");
     return;
 }

 $result = pg_query($Link, "select all_repayment();"); 
 if (!($result)) {
     echo_result(2, pg_last_error($Link) );
     pg_query($Link, "rollback");
     return;
 } 
 
 $result = pg_query($Link, "select calc_saldo($curr_mmgg,10,$id_paccnt);");
 if (!($result)) {
     echo_result(2, pg_last_error($Link) );
     pg_query($Link, "rollback");
     return;
 } 
 
}


$SQL = "update acm_correct_paccnt set is_calc = 1 where 
    mmgg = fun_mmgg() and id_paccnt = $id_paccnt and mmgg_corr = $mmgg ; ";
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
