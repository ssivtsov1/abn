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

$p_id=sql_field_val('id','int');
$p_id_pack=sql_field_val('id_pack','int');
$p_indic = sql_field_val('indic','int');
$p_dt_indic = sql_field_val('dt_indic','date');
$p_indic_real = sql_field_val('indic_real','int');
$p_id_operation = sql_field_val('id_operation','int');

if ($oper=="edit") {

 $QE = "update ind_pack_data set indic = $p_indic, dt_indic = $p_dt_indic, id_person = $session_user,
        indic_real = $p_indic_real, id_operation = $p_id_operation, dt_input = now()
          where id_pack = $p_id_pack and id = $p_id;";          


 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data updated');}
 else        {echo_result(2,pg_last_error($Link));}
}

if ($oper=="del") {

 $QE = "delete from ind_pack_data where id_pack = $p_id_pack and id = $p_id;";          


 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(-1,'Data deleted');}
 else        {echo_result(2,pg_last_error($Link));}
}

//------------------------------------------------------

}

}
catch (Exception $e) {

 echo echo_result(1,'Error: '.$e->getMessage());
}

?>