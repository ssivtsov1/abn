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

$table_name = 'adi_class_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_parent = sql_field_val('id_parent', 'int');
    $p_name = sql_field_val('name', 'string');
    $p_name_full = sql_field_val('name_full', 'string');
    $p_name_old = sql_field_val('name_old', 'string');
    $p_ident = sql_field_val('ident', 'string');
    $p_idk_class = sql_field_val('idk_class', 'int');
    $p_indx = sql_field_val('indx', 'string');

//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET name=$p_name, id_parent = $p_id_parent, name_old = $p_name_old,
        name_full=$p_name_full,  ident = $p_ident, idk_class = $p_idk_class,
        indx = $p_indx
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

      $QE = "INSERT INTO $table_name (id, id_parent, name,name_full, ident, indx, idk_class, name_old)
      VALUES(DEFAULT, $p_id_parent, $p_name,$p_name_full, $p_ident, $p_indx, $p_idk_class, $p_name_old) returning id";

      $res_e = pg_query($Link, $QE);
      if ($res_e)
      {    
        $row = pg_fetch_array($res_e);     
        echo_result(-1,'Data ins',$row['id']);
      }
      else
        echo_result(2, pg_last_error($Link).$QE);
    }
//------------------------------------------------------
    if ($oper == "del") {

      $QE = "DELETE FROM $table_name WHERE id= $p_id;";

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(-2, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>