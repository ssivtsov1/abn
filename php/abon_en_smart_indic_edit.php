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

$table_name = 'ind_smart_indics_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_indic = sql_field_val('indic', 'int');
    $p_ind_date = sql_field_val('ind_date', 'date');
    
    $p_id_usr = $session_user;
    
    
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "update $table_name set
        id_paccnt = $p_id_paccnt, indic = $p_indic, ind_date = $p_ind_date
        where id = $p_id;"; 

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(1, 'Data updated');
      } else {
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    
    if ($oper == "del") {

      $QE = "delete from $table_name where id = $p_id;";                 

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


