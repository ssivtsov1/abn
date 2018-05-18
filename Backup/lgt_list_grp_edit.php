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

$table_name = 'lgi_group_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_nm = sql_field_val('name', 'string');
    $p_bill_name = sql_field_val('bill_name', 'string');
    $p_ident = sql_field_val('ident', 'string');
    $p_alt_code = sql_field_val('alt_code', 'int');
    $p_kfk_code = sql_field_val('kfk_code', 'string');
    
    $p_id_kategor = sql_field_val('id_kategor', 'int');
    $p_id_budjet = sql_field_val('id_budjet', 'int');
    $p_id_document = sql_field_val('id_document', 'int');
    $p_id_state = sql_field_val('id_state', 'int');
    $p_id_calc = sql_field_val('id_calc', 'int');
    
    $p_dt_b = sql_field_val('dt_b', 'date');
    $p_dt_e = sql_field_val('dt_e', 'date');
    
//------------------------------------------------------
    if ($oper == "edit") {

        $p_change_date = sql_field_val('change_date', 'date');
        
        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

        $QE = "select lgi_change_fun(2,$p_id,$p_change_date,$session_user,1)";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
            return;
        }
        
        
      $QE = "UPDATE $table_name
        SET name=$p_nm, bill_name = $p_bill_name, alt_code = $p_alt_code,kfk_code = $p_kfk_code,
        id_budjet=$p_id_budjet, id_document = $p_id_document, id_state = $p_id_state,
        ident = $p_ident, id_kategor = $p_id_kategor, id_calc = $p_id_calc,
        dt_b = $p_dt_b, dt_e = $p_dt_e
        WHERE id=$p_id;";

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        pg_query($Link, "commit");   
        echo_result(1, 'Data updated');
      } else {
        echo_result(2, pg_last_error($Link));
        pg_query($Link, "rollback");
      }
    }
//------------------------------------------------------
    if ($oper == "add") {

        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

        $QE = "select lgi_change_fun(1,null,$p_dt_b,$session_user,1)";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
            return;
        }
        
        
      $QE = "INSERT INTO $table_name (name, bill_name, id_budjet, id_document, id_state, dt_b,dt_e,ident,id_kategor, id_calc, alt_code,kfk_code)
      VALUES($p_nm, $p_bill_name, $p_id_budjet, $p_id_document, $p_id_state, $p_dt_b,$p_dt_e,$p_ident,$p_id_kategor, $p_id_calc, $p_alt_code,$p_kfk_code)";

      $res_e = pg_query($Link, $QE);
      if ($res_e){
        pg_query($Link, "commit");   
        echo_result(-1, 'Data ins');
      }
      else
      {
          echo_result(2, pg_last_error($Link));
          pg_query($Link, "rollback");            
      }
    }
//------------------------------------------------------
    if ($oper == "del") {

        $p_change_date = sql_field_val('change_date', 'date');
        
        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

        $QE = "select lgi_change_fun(3,$p_id,$p_change_date,$session_user,1)";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
            return;
        }        
        
        
      $QE = "DELETE FROM $table_name WHERE id = $p_id;";

      $res_e = pg_query($Link, $QE);

      if ($res_e){
        echo_result(-1, 'Data deleted');
        pg_query($Link, "commit");   
      }
      else{
        echo_result(2, pg_last_error($Link));
        pg_query($Link, "rollback");
      }
    }
  }
} catch (Exception $e) {

  echo echo_result(2, 'Error: ' . $e->getMessage());
}
?>


