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
$p_id_street=sql_field_val('id_street','int');    
$p_floors=sql_field_val('floors','int');    
$p_build = sql_field_val('build','string');


//------------------------------------------------------
if ($oper=="edit") {

$QE="UPDATE adi_multifloor_tbl
   SET id_street=$p_id_street, build=$p_build, floors=$p_floors
 WHERE id = $p_id;";

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data updated');}
 else        {echo_result(2,pg_last_error($Link));}
}
//------------------------------------------------------
if ($oper=="add") {

$QE="INSERT INTO adi_multifloor_tbl(
            id_street, build, floors)
 values( $p_id_street, $p_build, $p_floors );"; 

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(-1,'Data ins');}
 else        {echo_result(2,pg_last_error($Link));}

 }
//------------------------------------------------------
if ($oper=="del") {

 
 $QE="Delete from adi_multifloor_tbl where id= $p_id;"; 

 $res_e=pg_query($Link,$QE);

 if ($res_e) {echo_result(1,'Data delated');}
 else        {echo_result(2,pg_last_error($Link));}
}


}
catch (Exception $e) {

 echo echo_result(1,'Error: '.$e->getMessage());
}

?>