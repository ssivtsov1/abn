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
$p_last_name = sql_field_val('last_name','string');
$p_patron_name = sql_field_val('patron_name','string');

$p_s_doc = sql_field_val('s_doc','string');
$p_n_doc = sql_field_val('n_doc','string');
$p_who_doc = sql_field_val('who_doc','string');
$p_dt_doc=sql_field_val('dt_doc','date');

$p_addr_reg=sql_field_val('addr_reg','record');
$p_addr_live=sql_field_val('addr_live','record');

$p_tax_number = sql_field_val('tax_number','string');
$p_home_phone = sql_field_val('home_phone','string');
$p_work_phone = sql_field_val('work_phone','string');
$p_mob_phone = sql_field_val('mob_phone','string');
$p_e_mail = sql_field_val('e_mail','string');

$p_note = sql_field_val('note','string');

$p_dt_b=sql_field_val('dt_b','date');
$p_dt_input=sql_field_val('dt_input','date');
$p_change_date=sql_field_val('change_date','date');
$p_id_usr = $session_user;

$p_id=sql_field_val('id','int');    

$result=pg_query($Link,"begin");
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   return;
 }    

 $QE="select clt_change_fun(22,null,$p_id,$p_change_date,$p_id_usr,1,0)";
 
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    


$QE="UPDATE clm_abon_tbl
   SET  last_name=$p_last_name, name=$p_name, patron_name=$p_patron_name, s_doc=$p_s_doc, 
       n_doc=$p_n_doc, dt_doc=$p_dt_doc, who_doc=$p_who_doc, 
       addr_reg=$p_addr_reg, addr_live=$p_addr_live, 
       tax_number=$p_tax_number, home_phone=$p_home_phone, note = $p_note, e_mail = $p_e_mail, 
       work_phone=$p_work_phone, mob_phone=$p_mob_phone, dt_b=$p_dt_b,id_person = $p_id_usr, 
       dt_input = now()
 WHERE id = $p_id;";

 $res_e=pg_query($Link,$QE);
 if ($res_e) {
     echo_result(1,'Data updated');
     pg_query($Link,"commit");   
     }
 else        {
     echo_result(2,pg_last_error($Link));
     pg_query($Link,"rollback");   
     }
}
//------------------------------------------------------
if ($oper=="add") {

$p_name = sql_field_val('name','string');
$p_last_name = sql_field_val('last_name','string');
$p_patron_name = sql_field_val('patron_name','string');

$p_s_doc = sql_field_val('s_doc','string');
$p_n_doc = sql_field_val('n_doc','string');
$p_who_doc = sql_field_val('who_doc','string');
$p_dt_doc=sql_field_val('dt_doc','date');

$p_addr_reg=sql_field_val('addr_reg','record');
$p_addr_live=sql_field_val('addr_live','record');

$p_tax_number = sql_field_val('tax_number','string');
$p_home_phone = sql_field_val('home_phone','string');
$p_work_phone = sql_field_val('work_phone','string');
$p_mob_phone = sql_field_val('mob_phone','string');
$p_e_mail = sql_field_val('e_mail','string');

$p_note = sql_field_val('note','string');
$p_dt_b=sql_field_val('dt_b','date');
$p_dt_input=sql_field_val('dt_input','date');

$p_work_period='null';
$p_id_usr = $session_user;

$result=pg_query($Link,"begin");
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   return;
 }    

 $QE="select clt_change_fun(21,null,null,$p_dt_b,$p_id_usr,1,0)";
 
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    

$QE="INSERT INTO clm_abon_tbl(id,
            last_name, name, patron_name, s_doc, n_doc, dt_doc, 
            who_doc, addr_reg,addr_live, tax_number, home_phone, work_phone, mob_phone, note,e_mail, 
            dt_b, work_period, id_person)
 values( DEFAULT, $p_last_name, $p_name, $p_patron_name, $p_s_doc, $p_n_doc, $p_dt_doc, 
            $p_who_doc, $p_addr_reg,$p_addr_live, $p_tax_number, $p_home_phone, $p_work_phone, $p_mob_phone, $p_note,$p_e_mail,
            $p_dt_b, $p_work_period, $p_id_usr ) returning id;"; 

 $res_e=pg_query($Link,$QE);
 if ($res_e) {
     
     $row = pg_fetch_array($res_e);     
     pg_query($Link,"commit");      
     
     $last_name = trim($_POST['last_name']);
     $name = trim($_POST['name']);
     $patron_name = trim($_POST['patron_name']);
     
     echo_result(-1,"$last_name $name $patron_name",$row['id']);
     }
 else{
     echo_result(2,pg_last_error($Link));
     pg_query($Link,"rollback");   
     }

 }
//------------------------------------------------------
if ($oper=="del") {

 $p_id=sql_field_val('id','int');    
 $p_change_date=sql_field_val('change_date','date');
 $p_id_usr = $session_user;

 
 $result=pg_query($Link,"begin");
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   return;
 }    

 $QE="select clt_change_fun(23,null,$p_id,$p_change_date,$p_id_usr,1,0)";
 
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    

 $QE="Delete from clm_abon_tbl where id= $p_id;"; 

 $res_e=pg_query($Link,$QE);

 if ($res_e) {
     echo_result(-1,'Data delated');
     pg_query($Link,"commit");   
     }
 else{
     echo_result(2,pg_last_error($Link));
     pg_query($Link,"rollback");   
     }
}


}
catch (Exception $e) {

 echo echo_result(1,'Error: '.$e->getMessage());
}

?>