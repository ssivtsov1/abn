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


try {

    $p_id = sql_field_val('id', 'int');
    $p_id_type_meter = sql_field_val('id_type_meter', 'int');
    $p_num_meter = sql_field_val('num_meter', 'string');
    $p_carry = sql_field_val('carry', 'int');
    
//------------------------------------------------------
    if ($p_id_type_meter != "null") {

      $QE = "  select meter_type_global_change($p_id,$p_id_type_meter); ";

      $res_e = pg_query($Link, $QE);
      if (!$res_e) {
        echo_result(2, pg_last_error($Link));
        return;
      }
    }
    
    if ($p_num_meter != "null") {

      $QE = "  select meter_num_global_change($p_id,$p_num_meter); ";

      $res_e = pg_query($Link, $QE);
      if (!$res_e) {
        echo_result(2, pg_last_error($Link));
        return;
      }
    }
    
    if ($p_carry != "null") {

      $QE = "  select meter_carry_global_change($p_id,$p_carry); ";

      $res_e = pg_query($Link, $QE);
      if (!$res_e) {
        echo_result(2, pg_last_error($Link));
        return;
      }
    }
    echo_result(-1,'Data upd ok!');
    
//------------------------------------------------------

    
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>