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
    $p_code = sql_field_val('code', 'int');
    
    $p_id_runner = sql_field_val('id_runner', 'int');
    $p_id_operator = sql_field_val('id_operator', 'int');
    $p_id_kontrol = sql_field_val('id_kontrol', 'int');
    $p_sort_flag = sql_field_val('sort_flag', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    $p_zone = sql_field_val('zone', 'string');
    
    $p_dt_b = sql_field_val('dt_b', 'date');
    
    $p_change_date=sql_field_val('change_date','date');
    $p_id_usr = $session_user;
    
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "select prs_runner_sector_edit_fun($p_id,$p_name,$p_id_runner,$p_notes,$p_change_date,$p_id_usr);"; 

      $res_e = pg_query($Link, $QE);
      if (!($res_e)) {
        echo_result(2, pg_last_error($Link) );
        return;
      }      
      
      $QE = "update prs_runner_sectors set sort_flag = $p_sort_flag, id_operator = $p_id_operator, zone = $p_zone, code = $p_code,
       id_region = $p_id_region  , id_kontrol = $p_id_kontrol  where id = $p_id;"; 

      $res_e = pg_query($Link, $QE);
      
      if ($res_e) {
        echo_result(1, 'Data updated');
      } else {
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    if ($oper == "add") {

      $QE = "select prs_runner_sector_new_fun($p_name,$p_id_runner,$p_notes,$p_dt_b,$p_id_usr) as id;";         

      $res_e = pg_query($Link, $QE);
      
      if (!($res_e)) {
        echo_result(2, pg_last_error($Link) );
        return;
      }         
      $row = pg_fetch_array($res_e);
      $p_id = $row['id'];
      
      $QE = "update prs_runner_sectors set sort_flag = $p_sort_flag, id_operator = $p_id_operator, zone = $p_zone,  code = $p_code,
      id_region = $p_id_region , id_kontrol = $p_id_kontrol where id = $p_id;"; 

      $res_e = pg_query($Link, $QE);
      if ($res_e)
        echo_result(1, 'Data ins');
      else
        echo_result(2, pg_last_error($Link));
    }
//------------------------------------------------------
    if ($oper == "del") {

      $QE = "select prs_runner_sector_del_fun($p_id,$p_change_date,$p_id_usr);";                 

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


