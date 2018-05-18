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

$table_name = 'prs_department';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_parent = sql_field_val('id_parent_department', 'int');
    $p_lvl = sql_field_val('lvl', 'int');
    
    $p_name = sql_field_val('name', 'string');
    $p_full_name = sql_field_val('full_name', 'string');

    $p_id_usr = $session_user; // где взять текущего юзера?    
 
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET id_parent_department=$p_id_parent, name=$p_name, full_name=$p_full_name
        WHERE id=$p_id;";

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
          
        pg_query($Link, 'select order_dep_fun(null, 0,0);');  
        
        echo_result(1, 'Data updated');
      } else {
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    if ($oper == "add") {

      $QE = "INSERT INTO $table_name (id_parent_department, lvl, name, full_name)
      VALUES($p_id_parent, $p_lvl, $p_name, $p_full_name)";

      $res_e = pg_query($Link, $QE);
      if ($res_e)
      {  
        pg_query($Link, 'select order_dep_fun(null, 0,0);');    
        echo_result(1, 'Data ins');
      }
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