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

$table_name = 'aqm_tarif_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_grptar = sql_field_val('id_grptar', 'int');
    $p_id_doc = sql_field_val('id_doc', 'int');
    
    $p_name = sql_field_val('name',   'string');
    $p_ident = sql_field_val('ident', 'string');
    
    $p_lim_min = sql_field_val('lim_min', 'int');
    $p_lim_max = sql_field_val('lim_max', 'int');
    $p_per_min = sql_field_val('per_min', 'date');
    $p_per_max = sql_field_val('per_max', 'date');

    $p_dt_b = sql_field_val('dt_b', 'date');
    $p_dt_e = sql_field_val('dt_e', 'date');
    
    

//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET id_doc=$p_id_doc, ident=$p_ident, lim_min=$p_lim_min, lim_max=$p_lim_max, dt_b=$p_dt_b, 
        dt_e=$p_dt_e, per_min=$p_per_min, per_max=$p_per_max, name=$p_name
        WHERE id=$p_id;";

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(1, 'Data updated'.$QE);
      } else {
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    if ($oper == "add") {

      $QE = "INSERT INTO $table_name (id_grptar, id_doc, ident, name, lim_min,lim_max,dt_b,dt_e,per_min,per_max)
      VALUES($p_id_grptar,$p_id_doc,$p_ident,$p_name,$p_lim_min,$p_lim_max,$p_dt_b,$p_dt_e,$p_per_min,$p_per_max)";

      $res_e = pg_query($Link, $QE);
      if ($res_e)
        echo_result(-1, 'Data ins');
      else
        echo_result(2, pg_last_error($Link).$QE);
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