<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

try {

if (isset($_POST['oper'])) {
$oper=$_POST['oper'];

//------------------------------------------------------
if ($oper=="edit") {

$p_dt_b=sql_field_val('dt_b','date');
$p_dt_e=sql_field_val('dt_e','date');

$p_id=sql_field_val('id','int');    

$QE="UPDATE clm_paccnt_h
   SET dt_b = $p_dt_b, dt_e = $p_dt_e
 WHERE id_key = $p_id;";

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data updated');}
 else        {echo_result(2,pg_last_error($Link));}
}
//------------------------------------------------------
if ($oper=="del") {

 $p_id=sql_field_val('id','int');    
 $QE="Delete from clm_paccnt_h where id_key= $p_id;"; 

 $res_e=pg_query($Link,$QE);

 if ($res_e) {echo_result(1,'Data delated');}
 else        {echo_result(2,pg_last_error($Link));}
}

}


}
catch (Exception $e) {

 echo echo_result(1,'Error: '.$e->getMessage());
}

?>