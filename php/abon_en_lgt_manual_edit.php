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
$table_name = 'clm_lgt_manual_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_mmgg_bill = sql_field_val('mmgg_bill', 'mmgg');
    $p_action = sql_field_val('id_action', 'int');
    
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET mmgg_bill=$p_mmgg_bill, id_action=$p_action,
        id_person = $session_user, id_paccnt = $p_id_paccnt 
        WHERE id=$p_id;";

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(1, 'Data updated');
      } else {
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    if ($oper == "add") {
        
   $QE = "INSERT INTO $table_name (id_paccnt, mmgg_bill, id_action,id_person )
      VALUES($p_id_paccnt, $p_mmgg_bill, $p_action,$session_user   )";
/*
      $res_e = pg_query($Link, $QE);
      if (!($res_e))
      {
        echo_result(2, pg_last_error($Link));
        return;
      }

   $QE = "INSERT INTO acm_correct_paccnt (id_paccnt, mmgg_corr, is_calc,id_person )
      VALUES($p_id_paccnt, $p_mmgg_bill, 0, $session_user   )";
*/
      $res_e = pg_query($Link, $QE);
      if ($res_e)
        echo_result(-1, 'Data insert');
      else
        echo_result(2, pg_last_error($Link));
      

 }
//------------------------------------------------------
    if ($oper == "del") {

      $QE = "DELETE FROM $table_name WHERE id= $p_id;";

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(-1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }
//------------------------------------------------------

    }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>