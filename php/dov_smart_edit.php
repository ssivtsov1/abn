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
$p_id=sql_field_val('id','int');    
$p_name = sql_field_val('name','string');
$p_path = sql_field_val('path','string');
$p_addr=sql_field_val('addr','record');
$p_ident = sql_field_val('ident','string');
$p_book = sql_field_val('book','string');
$p_code = sql_field_val('code','string');

//------------------------------------------------------
if ($oper=="edit") {



$QE="UPDATE ind_smart_house_tbl
   SET name=$p_name, addr=$p_addr, book=$p_book, code=$p_code, ident=$p_ident,
    path=$p_path
 WHERE id = $p_id;";

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data updated');}
 else        {echo_result(2,pg_last_error($Link));}
}
//------------------------------------------------------
if ($oper=="add") {


$QE="INSERT INTO ind_smart_house_tbl(
            name, path, addr, ident, book, code)
 values( $p_name, $p_path, $p_addr, $p_ident, $p_book, $p_code);"; 

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(-1,'Data ins');}
 else        {echo_result(2,pg_last_error($Link));}

 }
//------------------------------------------------------
if ($oper=="del") {

  $QE="Delete from ind_smart_house_tbl where id= $p_id;"; 

 $res_e=pg_query($Link,$QE);

 if ($res_e) {echo_result(-1,'Data delated');}
 else        {echo_result(2,pg_last_error($Link));}
}


}
catch (Exception $e) {

 echo echo_result(1,'Error: '.$e->getMessage());
}

?>