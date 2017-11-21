<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

try {

    
if (isset($_POST['operation'])) 
{    
 $oper=$_POST['operation'];
}

//echo $oper;

$p_id_usr = $session_user; // где взять текущего юзера?    
$p_change_date = sql_field_val('change_date','date');

$result=pg_query($Link,"begin");
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   return;
 }    

//------------------------------------------------------
if ($oper=="node_del") {

 $p_id=sql_field_val('id','int');    
 
 $QE="select eqt_change_fun(3,null,null,$p_change_date,$p_id_usr,$p_id,null,1)";
 
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    
 
 
 $QE="Delete from eqm_equipment_tbl where id= $p_id;"; 

 $res_e=pg_query($Link,$QE);

 if ($res_e) {
     echo_result(-1,'Data delated');
     pg_query($Link,"commit");   
     return;
     
     }
 else {
     echo_result(2,pg_last_error($Link));
     pg_query($Link,"rollback");   
     return;
     }
}
//------------------------------------------------------
if ($oper=="meter_connect") {
 //echo '123';
 $p_id=sql_field_val('id','int');    
 $p_id_paccnt=sql_field_val('id_paccnt','int');    
 
 $QE="select eqt_change_fun(31,null,null,fun_mmgg()::date,$p_id_usr,$p_id,null,1)";
 //echo $QE;
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    
 
 $QE="update clm_meterpoint_tbl set code_eqp = $p_id where id_paccnt = $p_id_paccnt;";
 //echo $QE;
 $res_e=pg_query($Link,$QE);

 if ($res_e) { 
     echo_result(-1,'Data ins');
     pg_query($Link,"commit");   
     return;
     
     }
 else {
     echo_result(2,pg_last_error($Link));
     pg_query($Link,"rollback");   
     return;
     }
}

//------------------------------------------------------
if ($oper=="meter_disconnect") {

 $p_id=sql_field_val('id','int');    
 
 $QE="select eqt_change_fun(31,null,null,fun_mmgg()::date,$p_id_usr,$p_id,null,1)";
 
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    
 
 $QE="update clm_meterpoint_tbl set code_eqp = null where id = $p_id";
 
 $res_e=pg_query($Link,$QE);

 if ($res_e) {
     echo_result(-1,'Data delated');
     pg_query($Link,"commit");   
     return;
     
     }
 else {
     echo_result(2,pg_last_error($Link));
     pg_query($Link,"rollback");   
     return;
     }
}
//------------------------------------------------------
/*
if ($oper=="node_move") {
    
 $p_id=sql_field_val('id','int');    
 $p_id_parent=sql_field_val('id_parent','int');    
 $p_id_tree=sql_field_val('id_tree','int');     

 $QE="select eqt_change_fun(5,null,$p_id_tree,$p_change_date,$p_id_usr,$p_id,null,1)";
 
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    
 
 $QE="update eqm_eqp_tree_tbl set code_eqp_e = $p_id_parent, id_tree = $p_id_tree where code_eqp= $p_id;"; 

 $res_e=pg_query($Link,$QE);

 if ($res_e) {
     echo_result(-1,'node moved');
     pg_query($Link,"commit");   
     return;
     }
 else {
     echo_result(2,pg_last_error($Link));
     pg_query($Link,"rollback");   
     return;
     }
}
*/
//------------------------------------------------------
/*
if ($oper=="tree_new") {
    
    
 $p_id_paccnt=sql_field_val('id_paccnt','int');    
 $p_name=sql_field_val('tree_name','string');    

 $QE="select eqt_change_fun(7,$p_id_paccnt,null,$p_change_date,$p_id_usr,null,null,1)";
 
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    
 
 $QE="insert into eqm_tree_tbl(name,id_paccnt,code_eqp) values ($p_name,$p_id_paccnt,null);"; 

 $res_e=pg_query($Link,$QE);

 if ($res_e) {
     echo_result(-1,'tree add');
     pg_query($Link,"commit");   
     return;
     }
 else {
     echo_result(2,pg_last_error($Link));
     pg_query($Link,"rollback");   
     return;
     }
}
*/
//------------------------------------------------------
/*
if ($oper=="tree_del") {

 $p_id_tree=sql_field_val('id','int');    
 
 $QE="select eqt_change_fun(3,null,$p_id_tree,$p_change_date,$p_id_usr,null,null,1)";
 
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    
 
 
 $QE="Delete from eqm_tree_tbl where id= $p_id_tree;"; 

 $res_e=pg_query($Link,$QE);

 if ($res_e) {
     echo_result(-1,'tree delated');
     pg_query($Link,"commit");   
     return;
     
     }
 else {
     echo_result(2,pg_last_error($Link));
     pg_query($Link,"rollback");   
     return;
     }
}
*/
//------------------------------------------------------
/*
if ($oper=="node_rename") {

 $p_id=sql_field_val('id','int');    
 $p_name=sql_field_val('new_name','string');    
 
 $QE="update eqm_eqp_tree_tbl set name = $p_name where code_eqp= $p_id;"; 

 $res_e=pg_query($Link,$QE);

 if ($res_e) {
     echo_result(-1,'node renamed');
     pg_query($Link,"commit");   
     return;     
     }
 else {
     echo_result(2,pg_last_error($Link));
     pg_query($Link,"rollback");   
     return;    
     }
}
*/

}
catch (Exception $e) {

 echo echo_result(1,'Error: '.$e->getMessage());
}

?>