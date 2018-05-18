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
$p_voltage_max=sql_field_val('voltage_max','numeric');
$p_amperage_max=sql_field_val('amperage_max','numeric');
$p_voltage2_nom=sql_field_val('voltage2_nom','numeric');
$p_amperage2_nom=sql_field_val('amperage2_nom','numeric');
$p_amperage_no_load=sql_field_val('amperage_no_load','numeric');
$p_power_nom=sql_field_val('power_nom','int');
$p_voltage_short_circuit=sql_field_val('voltage_short_circuit','numeric');
$p_iron=sql_field_val('iron','numeric');
$p_copper=sql_field_val('copper','numeric');
$p_show_def=sql_field_val('show_def','int');
$p_id=sql_field_val('id','int');    

$QE="UPDATE eqi_compensator_tbl
   SET name=$p_name, normative=$p_normative, voltage_nom=$p_voltage_nom, amperage_nom=$p_amperage_nom,
   voltage2_nom=$p_voltage2_nom, amperage2_nom=$p_amperage2_nom,
   voltage_max=$p_voltage_max, amperage_max=$p_amperage_max,
   amperage_no_load=$p_amperage_no_load, power_nom=$p_power_nom,  voltage_short_circuit=$p_voltage_short_circuit,
   iron=$p_iron, copper=$p_copper, show_def=$p_show_def
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
$p_voltage2_nom=sql_field_val('voltage2_nom','numeric');
$p_amperage2_nom=sql_field_val('amperage2_nom','numeric');
$p_voltage_max=sql_field_val('voltage_max','numeric');
$p_amperage_max=sql_field_val('amperage_max','numeric');

$p_amperage_no_load=sql_field_val('amperage_no_load','numeric');
$p_power_nom=sql_field_val('power_nom','int');
$p_voltage_short_circuit=sql_field_val('voltage_short_circuit','numeric');
$p_iron=sql_field_val('iron','numeric');
$p_copper=sql_field_val('copper','numeric');
$p_show_def=sql_field_val('show_def','int');

$QE="INSERT INTO eqi_compensator_tbl(
            name, normative, voltage_nom, amperage_nom, voltage_max, 
            amperage_max, voltage2_nom, amperage2_nom, amperage_no_load, 
            power_nom, voltage_short_circuit, iron, copper, show_def)
 values( $p_name, $p_normative, $p_voltage_nom, $p_amperage_nom, $p_voltage_max, 
            $p_amperage_max, $p_voltage2_nom, $p_amperage2_nom, $p_amperage_no_load, 
            $p_power_nom, $p_voltage_short_circuit, $p_iron, $p_copper, $p_show_def );"; 

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data ins');}
 else        {echo_result(2,pg_last_error($Link));}

 }
//------------------------------------------------------
if ($oper=="del") {

 $p_id=sql_field_val('id','int');    
 $QE="Delete from eqi_compensator_tbl where id= $p_id;"; 

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