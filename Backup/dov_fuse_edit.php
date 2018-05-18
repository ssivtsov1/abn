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

$p_name = sql_field_val('name','string');
$p_normative=sql_field_val('normative','string');
$p_voltage_nom=sql_field_val('voltage_nom','numeric');
$p_amperage_nom=sql_field_val('amperage_nom','numeric');
$p_power_nom=sql_field_val('power_nom','numeric');

$p_id=sql_field_val('id','int');    

$QE="UPDATE eqi_fuse_tbl
   SET name=$p_name, normative=$p_normative, voltage_nom=$p_voltage_nom, amperage_nom=$p_amperage_nom,
   power_nom=$p_power_nom
 WHERE id = $p_id;";

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data updated');}
 else        {echo_result(2,pg_last_error($Link));}
}
//------------------------------------------------------
if ($oper=="add") {

$p_name = sql_field_val('name','string');
$p_normative=sql_field_val('normative','string');
$p_voltage_nom=sql_field_val('voltage_nom','numeric');
$p_amperage_nom=sql_field_val('amperage_nom','numeric');
$p_power_nom=sql_field_val('power_nom','numeric');

$QE="INSERT INTO eqi_fuse_tbl(
            name, normative, voltage_nom, amperage_nom, power_nom )
 values( $p_name, $p_normative, $p_voltage_nom, $p_amperage_nom, $p_power_nom );"; 

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data ins');}
 else        {echo_result(2,pg_last_error($Link));}

 }
//------------------------------------------------------
if ($oper=="del") {

 $p_id=sql_field_val('id','int');    
 $QE="Delete from eqi_fuse_tbl where id= $p_id;"; 

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