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
 
  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    $p_ident = sql_field_val('ident', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_code = sql_field_val('code', 'string');
    
    
    $p_id_usr = $session_user; 
    

//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE aci_subs_tbl  SET id_paccnt=$p_id_paccnt, book = $p_book, code = $p_code,
        ident = $p_ident WHERE id=$p_id;";
      //echo $QE;
      $result = pg_query($Link, $QE);
      if (!($result)) 
      {
        echo_result(2,pg_last_error($Link));
        return;
      }    
      
      echo_result(1, 'Data updated');
      
    }
//------------------------------------------------------
    if ($oper == "add") {
/*
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
      */
    }
//------------------------------------------------------
    if ($oper == "del") {
/*
      $QE = "DELETE FROM $table_name WHERE id= $p_id;";

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(-1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
      */
    } 
//------------------------------------------------------
    
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>