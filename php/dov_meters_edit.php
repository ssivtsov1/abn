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
$p_kind_meter=sql_field_val('kind_meter','int');
$p_phase=sql_field_val('phase','int');
$p_carry=sql_field_val('carry','int');
$p_zones=sql_field_val('zones','int');
$p_zone_time_min=sql_field_val('zone_time_min','int');
$p_term_control=sql_field_val('term_control','int');
$p_show_def=sql_field_val('show_def','int');
$p_buffle=sql_field_val('buffle','numeric');
$p_cl=sql_field_val('cl','numeric');
$p_ae=sql_field_val('ae','int');
$p_re=sql_field_val('re','int');
$p_aeg=sql_field_val('aeg','int');
$p_reg=sql_field_val('reg','int');

$p_id=sql_field_val('id','int');    

$QE="UPDATE eqi_meter_vw
   SET name=$p_name, normative=$p_normative, voltage_nom=$p_voltage_nom, amperage_nom=$p_amperage_nom, kind_meter=$p_kind_meter, 
       phase=$p_phase, carry=$p_carry, zones=$p_zones, zone_time_min=$p_zone_time_min, term_control=$p_term_control, show_def=$p_show_def, 
       buffle=$p_buffle, cl=$p_cl , ae = $p_ae, re = $p_re, aeg = $p_aeg, reg = $p_reg
 WHERE id = $p_id;";

 //echo json_encode(array('errMess'=>'Error: '));

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
$p_kind_meter=sql_field_val('kind_meter','int');
$p_phase=sql_field_val('phase','int');
$p_carry=sql_field_val('carry','int');
$p_zones=sql_field_val('zones','int');
$p_zone_time_min=sql_field_val('zone_time_min','int');
$p_term_control=sql_field_val('term_control','int');
$p_show_def=sql_field_val('show_def','int');
$p_buffle=sql_field_val('buffle','numeric');
$p_cl=sql_field_val('cl','numeric');
$p_ae=sql_field_val('ae','int');
$p_re=sql_field_val('re','int');
$p_aeg=sql_field_val('aeg','int');
$p_reg=sql_field_val('reg','int');

//$nm_e=addcslashes($nm_e,"'");

 $QE="select eqi_meternew_fun( $p_name, $p_normative, $p_voltage_nom, $p_amperage_nom, $p_kind_meter, $p_phase, 
            $p_carry, $p_zones, $p_zone_time_min, $p_term_control, $p_show_def, $p_buffle, 
            $p_cl,$p_ae,$p_re,$p_aeg,$p_reg );"; 

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data ins');}
 else        {echo_result(2,pg_last_error($Link));}

 }
//------------------------------------------------------
if ($oper=="del") {

 $p_id=sql_field_val('id','int');    
 $QE="Delete from eqi_meter_tbl where id= $p_id;"; 

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