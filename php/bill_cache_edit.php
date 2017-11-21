<?php

header('Content-type: text/html; charset=utf-8');

include_once 'abon_en_func.php';
include_once 'abon_ded_func.php';


session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();
$table_name = 'act_bill_cache_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];
 
    $p_id = sql_field_val('id_doc', 'int');
    //$p_id_paccnt = sql_field_val('id_paccnt', 'int');

    $p_id_usr = $session_user;
    
    
//------------------------------------------------------
    if ($oper == "add") {

      $QE = "insert into act_bill_cache_tbl (id_doc,id_user) 
        values($p_id, $session_user) ;";
        
      $res_e = pg_query($Link, $QE);
      
        if (!($res_e)) 
        {
            echo_result(2,pg_last_error($Link));
            return;
        }    
        
      if ($res_e){
        echo_result(-1, 'Data ins',$id_doc);
      }
      else{
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    if ($oper == "del") {

      $QE = "delete from act_bill_cache_tbl where id_doc = $p_id and id_user = $session_user ;";                 

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(-1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }
//------------------------------------------------------
    if ($oper == "delall") {

      $QE = "delete from act_bill_cache_tbl where id_user = $session_user ;";

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(-1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }    
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


