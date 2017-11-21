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

$table_name = 'lgi_calc_header_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_nm = sql_field_val('name', 'string');
    $p_ident = sql_field_val('ident', 'string');
    $p_id_calc_type = sql_field_val('id_calc_type', 'int');
    $p_dt_b = sql_field_val('dt_b', 'date');
    $p_dt_e = sql_field_val('dt_e', 'date');
    
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET name=$p_nm, id_calc_type=$p_id_calc_type, ident = $p_ident, 
        dt_b = $p_dt_b, dt_e = $p_dt_e
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

      $QE = "INSERT INTO $table_name (name, id_calc_type,dt_b,dt_e, ident)
      VALUES($p_nm, $p_id_calc_type, $p_dt_b, $p_dt_e, $p_ident)";

      $res_e = pg_query($Link, $QE);
      if ($res_e)
        echo_result(1, 'Data ins');
      else
        echo_result(2, pg_last_error($Link).$QE);
    }
//------------------------------------------------------
    if ($oper == "del") {

      $QE = "DELETE FROM $table_name WHERE id = $p_id;";

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


