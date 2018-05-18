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

$table_name = 'lgi_norm_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_grp_lgt = sql_field_val('id_grp_lgt', 'int');
    $p_id_tar_grp = sql_field_val('id_tar_grp', 'int');
    $p_percent = sql_field_val('percent', 'numeric');
    
    $p_norm_min = sql_field_val('norm_min', 'numeric');
    $p_norm_one = sql_field_val('norm_one', 'numeric');
    $p_norm_max = sql_field_val('norm_max', 'numeric');

    $p_norm_heat_demand = sql_field_val('norm_heat_demand', 'numeric');
    $p_norm_heat_one = sql_field_val('norm_heat_one', 'numeric');
    $p_norm_heat_family = sql_field_val('norm_heat_family', 'numeric');
    
    $p_dt_b = sql_field_val('dt_b', 'date');
    $p_dt_e = sql_field_val('dt_e', 'date');
    
 
    

//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET percent=$p_percent, norm_min=$p_norm_min,norm_one=$p_norm_one,norm_max=$p_norm_max,
        id_tar_grp = $p_id_tar_grp, norm_heat_demand = $p_norm_heat_demand, norm_heat_one = $p_norm_heat_one, norm_heat_family = $p_norm_heat_family,
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

      $QE = "INSERT INTO $table_name (id_calc, id_tar_grp, percent, norm_min, norm_one, norm_max,norm_heat_demand,norm_heat_one,norm_heat_family, dt_b,dt_e)
      VALUES($p_id_grp_lgt, $p_id_tar_grp,$p_percent, $p_norm_min, $p_norm_one, $p_norm_max, $p_norm_heat_demand,$p_norm_heat_one,$p_norm_heat_family, $p_dt_b,$p_dt_e)";

      $res_e = pg_query($Link, $QE);
      if ($res_e)
        echo_result(1, 'Data ins');
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