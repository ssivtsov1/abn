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

$table_name = 'lgi_kategor_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_parent = sql_field_val('id_parent', 'int');
    $p_lvl = sql_field_val('lvl', 'int');
    $p_sort = sql_field_val('sort', 'int');
    
    $p_name = sql_field_val('name', 'string');
    $p_ident = sql_field_val('ident', 'string');
    $p_id_document = sql_field_val('id_document', 'int');
    
    $p_dt_b = sql_field_val('dt_b', 'date');
    $p_dt_e = sql_field_val('dt_e', 'date');

    $p_id_usr = $session_user; // где взять текущего юзера?    
 
    

//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET id_parent=$p_id_parent, name=$p_name,ident=$p_ident,id_document=$p_id_document,
        dt_b = $p_dt_b, dt_e = $p_dt_e ,id_person = $p_id_usr 
        WHERE id=$p_id;";

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
          
        pg_query($Link, 'select order_lgt_fun(null, 0,0);');  
        
        echo_result(1, 'Data updated');
      } else {
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    if ($oper == "add") {

      $QE = "INSERT INTO $table_name (id_parent, lvl, name, ident, id_document, dt_b, dt_e,  
            id_person, sort)
      VALUES($p_id_parent, $p_lvl, $p_name, $p_ident, $p_id_document, $p_dt_b, $p_dt_e,  
            $p_id_usr, $p_sort)";

      $res_e = pg_query($Link, $QE);
      if ($res_e)
      {  
        pg_query($Link, 'select order_lgt_fun(null, 0,0);');    
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