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
$p_voltage2_nom=sql_field_val('voltage2_nom','numeric');
$p_amperage2_nom=sql_field_val('amperage2_nom','numeric');
$p_conversion=sql_field_val('conversion','int');
$p_phase=sql_field_val('phase','int');

$p_id=sql_field_val('id','int');    

$QE="UPDATE eqi_compensator_i_tbl
   SET name=$p_name, normative=$p_normative, voltage_nom=$p_voltage_nom, amperage_nom=$p_amperage_nom,
   voltage2_nom=$p_voltage2_nom, amperage2_nom=$p_amperage2_nom,
   conversion=$p_conversion, phase=$p_phase
 WHERE id = $p_id;";

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data updated'.$QE);}
 else        {echo_result(2,pg_last_error($Link));}
}
//------------------------------------------------------
if ($oper=="add") {

$p_name = sql_field_val('name','string');
$p_normative=sql_field_val('normative','string');
$p_voltage_nom=sql_field_val('voltage_nom','numeric');
$p_amperage_nom=sql_field_val('amperage_nom','numeric');
$p_voltage2_nom=sql_field_val('voltage2_nom','numeric');
$p_amperage2_nom=sql_field_val('amperage2_nom','numeric');
$p_conversion=sql_field_val('conversion','int');
$p_phase=sql_field_val('phase','int');

$QE="INSERT INTO eqi_compensator_i_tbl(
            name, normative, voltage_nom, amperage_nom, voltage2_nom, amperage2_nom, conversion, phase)
 values( $p_name, $p_normative, $p_voltage_nom, $p_amperage_nom, $p_voltage2_nom, $p_amperage2_nom,
$p_conversion, $p_phase );"; 

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data ins');}
 else        {echo_result(2,pg_last_error($Link));}

 }
//------------------------------------------------------
if ($oper=="del") {

 $p_id=sql_field_val('id','int');    
 $QE="Delete from eqi_compensator_i_tbl where id= $p_id;"; 

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