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
$p_ident = sql_field_val('ident','string');
$p_rule=sql_field_val('default_rule','int');    

//------------------------------------------------------
if ($oper=="edit") {

$QE="UPDATE syi_enviroment
   SET name=$p_name, ident=$p_ident, default_rule=$p_rule
 WHERE id = $p_id;";

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(1,'Data updated');}
 else        {echo_result(2,pg_last_error($Link));}
}
//------------------------------------------------------
if ($oper=="add") {

$QE="INSERT INTO syi_enviroment( name, ident, default_rule)
 values( $p_name, $p_ident, $p_rule );"; 

 $res_e=pg_query($Link,$QE);
 if ($res_e) {echo_result(-1,'Data ins');}
 else        {echo_result(2,pg_last_error($Link));}

 }
//------------------------------------------------------
if ($oper=="del") {

 
 $QE="Delete from syi_enviroment where id= $p_id;"; 

 $res_e=pg_query($Link,$QE);

 if ($res_e) {echo_result(-1,'Data delated');}
 else        {echo_result(2,pg_last_error($Link));}
}


}
catch (Exception $e) {

 echo echo_result(1,'Error: '.$e->getMessage());
}

?>