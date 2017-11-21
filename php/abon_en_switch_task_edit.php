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

$table_name = 'clm_tasks_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_idk_work = sql_field_val('idk_work', 'int');
    $p_idk_reason = sql_field_val('idk_reason', 'int');
    $p_idk_abn_state = sql_field_val('idk_abn_state', 'int');
    $p_task_state = sql_field_val('task_state', 'int');
    $p_task_num = sql_field_val('act_num', 'string'); 
    $p_date_print = sql_field_val('dt_create', 'date');
    $p_date_work = sql_field_val('dt_action', 'date');
    $p_sum_warning = sql_field_val('sum_warning', 'numeric');
    $p_note = sql_field_val('comment', 'string');
    
    
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET  id_paccnt=$p_id_paccnt, idk_work=$p_idk_work, idk_reason=$p_idk_reason, idk_abn_state=$p_idk_abn_state, 
        task_num=$p_task_num, date_print=$p_date_print, date_work=$p_date_work, sum_warning=$p_sum_warning,
        task_state=$p_task_state, note=$p_note
        WHERE id=$p_id;";

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(1, 'Data updated');
      } else {
        echo_result(2, pg_last_error($Link));
      }
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