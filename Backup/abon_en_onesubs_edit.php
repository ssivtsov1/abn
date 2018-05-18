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
$table_name = 'acm_subs_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_mmgg = sql_field_val('mmgg', 'mmgg');
    $p_dt_b = sql_field_val('dt_b', 'date');
    $p_dt_e = sql_field_val('dt_e', 'date');
    
    $p_sum_subs = sql_field_val('sum_subs', 'numeric');
    $p_sum_recalc = sql_field_val('sum_recalc', 'numeric');
    $p_ob_pay = sql_field_val('ob_pay', 'numeric');
    $p_kol_subs = sql_field_val('kol_subs', 'numeric');
    
    
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET ob_pay=$p_ob_pay, kol_subs=$p_kol_subs
        WHERE id=$p_id;";

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(1, 'Data updated');
      } else {
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
   /* if ($oper == "add") {

      $QE = "INSERT INTO $table_name (id_paccnt, dt_action, dt_warning, action, comment, 
        dt_create,dt_sum,sum_warning,id_person)
      VALUES($p_id_paccnt, $p_dt_action, $p_dt_warning, $p_action, $p_comment, 
        $p_dt_create , $p_dt_sum , $p_sum_warning ,$session_user)";

      $res_e = pg_query($Link, $QE);
      if ($res_e)
        echo_result(-1, 'Data ins');
      else
        echo_result(2, pg_last_error($Link).$QE);
    }*/
//------------------------------------------------------
/*
    if ($oper == "del") {

      $QE = "DELETE FROM $table_name WHERE id= $p_id;";

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(-1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }
    */
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>