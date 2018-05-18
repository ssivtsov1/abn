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

$p_materal=sql_field_val('materal','int');
$p_cord_diam=sql_field_val('cord_diam','nuneric');
$p_cord_qn=sql_field_val('cord_qn','int');
$p_s_nom=sql_field_val('s_nom','nuneric');
$p_ro=sql_field_val('ro','nuneric');
$p_xo=sql_field_val('xo','nuneric');
$p_dpo=sql_field_val('dpo','nuneric');
$p_show_def=sql_field_val('show_def','int');
$p_id=sql_field_val('id','int');    

$QE="UPDATE eqi_corde_tbl
   SET name=$p_name, normative=$p_normative, voltage_nom=$p_voltage_nom, amperage_nom=$p_amperage_nom,
   voltage_max=$p_voltage_max, amperage_max=$p_amperage_max,
   materal=$p_materal, cord_diam=$p_cord_diam, cord_qn=$p_cord_qn, dpo=$p_dpo, xo=$p_xo, 
   ro=$p_ro, show_def=$p_show_def, s_nom=$p_s_nom
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
$p_voltage_max=sql_field_val('voltage_max','numeric');
$p_amperage_max=sql_field_val('amperage_max','numeric');

$p_materal=sql_field_val('materal','int');
$p_cord_diam=sql_field_val('cord_diam','nuneric');
$p_cord_qn=sql_field_val('cord_qn','int');
$p_s_nom=sql_field_val('s_nom','nuneric');
$p_ro=sql_field_val('ro','nuneric');
$p_xo=sql_field_val('xo','nuneric');
$p_dpo=sql_field_val('dpo','nuneric');
$p_show_def=sql_field_val('show_def','int');

$QE="INSERT INTO eqi_corde_tbl(
            name, normative, voltage_nom, amperage_nom, voltage_max, 
            amperage_max, materal, cord_diam, cord_qn, dpo, xo, ro, show_def, s_nom)
 values( $p_name, $p_normative, $p_voltage_nom, $p_amperage_nom, $p_voltage_max, $p_amperage_max,
$p_materal, $p_cord_diam, $p_cord_qn, $p_dpo, $p_xo, $p_ro, $p_show_def, $p_s_nom );"; 

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data ins');}
 else        {echo_result(2,pg_last_error($Link));}

 }
//------------------------------------------------------
if ($oper=="del") {

 $p_id=sql_field_val('id','int');    
 $QE="Delete from eqi_corde_tbl where id= $p_id;"; 

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