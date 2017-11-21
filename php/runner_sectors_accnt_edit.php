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


$table_name = 'prs_runner_sectors';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_name = sql_field_val('name', 'string');
    $p_notes = sql_field_val('notes', 'string');
    
    $p_id_runner = sql_field_val('id_runner', 'int');
   
    $p_dt_b = sql_field_val('dt_b', 'date');
    
    $p_change_date=sql_field_val('change_date','date');
    $p_id_usr = $session_user;
    
    
    
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET name=$p_name, id_runner=$p_id_runner, notes = $p_notes, dt_b = $p_dt_b
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

      $QE = "INSERT INTO $table_name (name , id_runner, notes, dt_b)
      VALUES($p_name,$p_id_runner, $p_notes, $p_dt_b)";

      $res_e = pg_query($Link, $QE);
      if ($res_e)
        echo_result(1, 'Data ins');
      else
        echo_result(2, pg_last_error($Link));
    }
//------------------------------------------------------
    if ($oper == "del") {

      $QE = "DELETE FROM $table_name WHERE id= $p_id;";

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


