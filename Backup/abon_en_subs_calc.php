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
/*
sleep(10);
echo_result(-1,'Subs calc ok!',$row['result']);
return;
 */
//$mmgg = sql_field_val('mmgg', 'date'); 

$result = pg_query($Link, "select crt_ttbl();");
if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    //pg_query($Link, "rollback");
    return;
}

$result = pg_query($Link, "insert into syi_sysvars_tmp (id, ident,type_ident, value_ident) 
     values(120,'id_person','int', $session_user);");

if (!($result)) {
    echo_result(2, pg_last_error($Link) );
//    pg_query($Link, "rollback");
    return;
}


$SQL = "select calc_subsid(fun_mmgg()::date, null)::int as result; ";
$result = pg_query($Link, $SQL);
if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    return;
}
$row = pg_fetch_array($result);     

 
$SQL = "select calc_subs_lgt_recalc(fun_mmgg()::date, $session_user)::int as result; ";
$result = pg_query($Link, $SQL);
if ($result) {
    
    echo_result(-1,'Subs calc ok!',$row['result']);

} else {
    echo_result(2, pg_last_error($Link));
}


?>
