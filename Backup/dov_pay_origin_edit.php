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
$p_mfo=sql_field_val('mfo','int');
$p_ident=sql_field_val('ident','string');
$p_id=sql_field_val('id','int');    
$p_id_edit=sql_field_val('id_edit','int');        

$QE="UPDATE aci_pay_origin_tbl
   SET id =$p_id_edit,  name=$p_name, mfo=$p_mfo ,ident = $p_ident WHERE id = $p_id;";

 //echo json_encode(array('errMess'=>'Error: '));

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data updated');}
 else        {echo_result(2,pg_last_error($Link));}
}
//------------------------------------------------------
if ($oper=="add") {

$p_id=sql_field_val('id','int');        
$p_id_edit=sql_field_val('id_edit','int');        
$p_name = sql_field_val('name','string');
$p_mfo=sql_field_val('mfo','int');
$p_ident=sql_field_val('ident','string');

 $QE="insert into aci_pay_origin_tbl(id, name, mfo, ident) values($p_id_edit, $p_name,$p_mfo,$p_ident) ;"; 

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data ins');}
 else        {echo_result(2,pg_last_error($Link));}

 }
//------------------------------------------------------
if ($oper=="del") {

 $p_id=sql_field_val('id','int');    
 $QE="Delete from aci_pay_origin_tbl where id= $p_id;"; 

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