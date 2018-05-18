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

$table_name = 'clm_plandemand_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    $p_id_zone = sql_field_val('id_zone', 'int');
    $p_kind_energy = 10; //sql_field_val('kind_energy', 'int');
    
    $p_mmgg = sql_field_val('mmgg', 'mmgg');
    $p_demand = sql_field_val('demand', 'int');
    

//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET id_zone=$p_id_zone, kind_energy=$p_kind_energy, mmgg=$p_mmgg, demand=$p_demand,
        id_person = $session_user, dt_input = now()
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

      $QE = "INSERT INTO $table_name (id_paccnt, id_zone, kind_energy, mmgg, demand, id_person)
      VALUES($p_id_paccnt, $p_id_zone, $p_kind_energy, $p_mmgg, $p_demand, $session_user)";

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