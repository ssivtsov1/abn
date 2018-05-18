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

//------------------------------------------------------
if ($oper=="edit") {

$p_name = sql_field_val('name','string');
$p_addr=sql_field_val('addr','record');
$p_id_fider=sql_field_val('id_fider','int');
$p_dt_install=sql_field_val('dt_install','date');
$p_id_voltage=sql_field_val('id_voltage','int');
$p_power=sql_field_val('power','numeric');
$p_abon_ps=sql_field_val('abon_ps','int');
$p_id=sql_field_val('id','int');    

$QE="UPDATE eqm_tp_tbl
   SET name=$p_name, addr=$p_addr, id_fider=$p_id_fider, dt_install=$p_dt_install,
   id_voltage=$p_id_voltage, power=$p_power, abon_ps=$p_abon_ps
 WHERE id = $p_id;";

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data updated');}
 else        {echo_result(2,pg_last_error($Link));}
}
//------------------------------------------------------
if ($oper=="add") {

$p_name = sql_field_val('name','string');
$p_addr=sql_field_val('addr','record');
$p_id_fider=sql_field_val('id_fider','int');
$p_dt_install=sql_field_val('dt_install','date');
$p_id_voltage=sql_field_val('id_voltage','int');
$p_power=sql_field_val('power','numeric');
$p_abon_ps=sql_field_val('abon_ps','int');

$QE="INSERT INTO eqm_tp_tbl(
            name, addr, id_fider, dt_install, id_voltage, power, abon_ps)
 values( $p_name, $p_addr, $p_id_fider, $p_dt_install, $p_id_voltage, $p_power, $p_abon_ps );"; 

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(-1,'Data ins');}
 else        {echo_result(2,pg_last_error($Link));}

 }
//------------------------------------------------------
if ($oper=="del") {

 $p_id=sql_field_val('id','int');    
 $QE="Delete from eqm_tp_tbl where id= $p_id;"; 

 $res_e=pg_query($Link,$QE);

 if ($res_e) {echo_result(1,'Data delated');}
 else        {echo_result(2,pg_last_error($Link));}
}


}
catch (Exception $e) {

 echo echo_result(1,'Error: '.$e->getMessage());
}

?>