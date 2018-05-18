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

$table_name = 'prs_persons';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_department = sql_field_val('id_department', 'int');
    $p_id_post = sql_field_val('id_post', 'int');
    $p_id_abon = sql_field_val('id_abon', 'int');
    
    $p_name = sql_field_val('name', 'string');
    $p_soname = sql_field_val('soname', 'string');
    $p_father_name = sql_field_val('father_name', 'string');
    $p_represent_name = sql_field_val('represent_name', 'string');
    $p_phone = sql_field_val('phone', 'string');
    
    $p_is_active = sql_field_val('is_active', 'int');
    $p_is_runner = sql_field_val('is_runner', 'int');
        
    $p_date_start = sql_field_val('date_start', 'date');
    $p_date_end = sql_field_val('date_end', 'date');
    
 
    

//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET id_department=$p_id_department, id_post=$p_id_post,id_abon=$p_id_abon,
        name=$p_name, soname=$p_soname, father_name=$p_father_name, represent_name=$p_represent_name,
        is_active = $p_is_active, is_runner = $p_is_runner ,
        date_start = $p_date_start, date_end = $p_date_end , phone = $p_phone
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

      $QE = "INSERT INTO $table_name (id_department, id_post, id_abon, name, soname, father_name, 
            represent_name, is_active, is_runner, phone, date_start, date_end)
      VALUES($p_id_department, $p_id_post, $p_id_abon, $p_name, $p_soname, $p_father_name, 
            $p_represent_name, $p_is_active, $p_is_runner, $p_phone, $p_date_start, $p_date_end)";

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