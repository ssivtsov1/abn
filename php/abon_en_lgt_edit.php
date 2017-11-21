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
$table_name = 'lgm_abon_load_tbl';

try {
 
  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    $p_code_lgt = sql_field_val('code_lgt', 'int');
    $p_id_lgt = sql_field_val('id_lgt', 'int');
    
    $p_date_b = sql_field_val('date_b', 'date');
    $p_date_e = sql_field_val('date_e', 'date');
    
    $p_status = sql_field_val('status', 'int');

    $p_id_usr = $session_user; // где взять текущего юзера?    
 
    

//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET id_paccnt=$p_id_paccnt, code_lgt=$p_code_lgt,id_lgt=$p_id_lgt,
        date_b=$p_date_b, date_e = $p_date_e, status = $p_status 
        WHERE id=$p_id;";

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
          
        //pg_query($Link, 'select order_lgt_fun(null, 0,0);');  
        
        echo_result(1, 'Data updated');
      } else {
        echo_result(2, pg_last_error($Link));
      }
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
        echo_result(1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
      */
    }
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>