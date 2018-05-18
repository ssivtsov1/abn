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

$table_name = 'syi_user';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_parent = sql_field_val('id_parent', 'int');
    $p_flag_type = sql_field_val('flag_type', 'int');
        
    $p_name = sql_field_val('name', 'string');
    
    $p_id_person = sql_field_val('id_person', 'int');
        

//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET id_parent=$p_id_parent, id_person=$p_id_person, name=$p_name 
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

      $QE = "INSERT INTO $table_name (id_parent, flag_type, name, id_person )
      VALUES($p_id_parent, $p_flag_type, $p_name, $p_id_person)";

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