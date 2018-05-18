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

    
if (isset($_POST['submitButton'])) 
{    
  $oper=$_POST['submitButton'];
}
else
{    
    if (isset($_POST['oper'])) {
        $oper=$_POST['oper'];
    }
}
    
//throw new Exception(json_encode($_POST));
$p_id=sql_field_val('id','int');    
$p_dt_b=sql_field_val('dt_b','date');
$p_dt_e=sql_field_val('dt_e','date');
$p_note = sql_field_val('note','string');
$p_floor=sql_field_val('floor','int');
$p_id_region=sql_field_val('id_region','int');


//------------------------------------------------------
if ($oper=="edit") {

$QE="UPDATE heat_period
   SET dt_b=$p_dt_b, dt_e=$p_dt_e, note=$p_note, floor = $p_floor, id_region = $p_id_region
 WHERE id = $p_id;";

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data updated');}
 else        {echo_result(2,pg_last_error($Link));}
}
//------------------------------------------------------
if ($oper=="add") {

$QE="INSERT INTO heat_period(
            dt_b, dt_e, note, floor, id_region)
 values($p_dt_b, $p_dt_e, $p_note,$p_floor, $p_id_region );"; 

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(-1,'Data ins');}
 else        {echo_result(2,pg_last_error($Link));}

 }
//------------------------------------------------------
if ($oper=="del") {

 
 $QE="Delete from heat_period where id= $p_id;"; 

 $res_e=pg_query($Link,$QE);

 if ($res_e) {echo_result(-1,'Data deleted');}
 else        {echo_result(2,pg_last_error($Link));}
}


}
catch (Exception $e) {

 echo echo_result(1,'Error: '.$e->getMessage());
}

?>