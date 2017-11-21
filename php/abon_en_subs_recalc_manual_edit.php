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
$table_name = 'rep_subs_recalc_month_manual_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    $p_id_manual = sql_field_val('id_manual', 'int');
    $p_mmgg_calc = sql_field_val('mmgg_calc', 'date');
    
  
    $p_subs_current = sql_field_val('subs_current', 'numeric');
    $p_ob_pay = sql_field_val('ob_pay', 'numeric');
    $p_bill_sum = sql_field_val('bill_sum', 'numeric');
    
    
//------------------------------------------------------
    if ($oper == "edit") {

     if ($p_id_manual!='null')   
     {
         
      $QE = "UPDATE rep_subs_recalc_month_manual_tbl
        SET ob_pay=$p_ob_pay, subs_current=$p_subs_current, bill_sum = $p_bill_sum, id_person = $session_user
        WHERE id=$p_id_manual;";

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(-1, 'Data updated');
      } else {
        echo_result(2, pg_last_error($Link));
      }
     }
     else
     {
      $QE = "insert into rep_subs_recalc_month_manual_tbl(id_paccnt, mmgg_calc,subs_current,ob_pay, bill_sum , id_person)
         values($p_id_paccnt, $p_mmgg_calc,$p_subs_current,$p_ob_pay, $p_bill_sum , $session_user);";

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(-1, 'Data inserted');
      } else {
        echo_result(2, pg_last_error($Link));
      }
         
     }
    }
//------------------------------------------------------
   /* if ($oper == "add") {

      $QE = "INSERT INTO $table_name (id_paccnt, dt_action, dt_warning, action, comment, 
        dt_create,dt_sum,sum_warning,id_person)
      VALUES($p_id_paccnt, $p_dt_action, $p_dt_warning, $p_action, $p_comment, 
        $p_dt_create , $p_dt_sum , $p_sum_warning ,$session_user)";

      $res_e = pg_query($Link, $QE);
      if ($res_e)
        echo_result(-1, 'Data ins');
      else
        echo_result(2, pg_last_error($Link).$QE);
    }*/
//------------------------------------------------------

    if ($oper == "del") {

     if ($p_id_manual!='null')   
     {
        
      $QE = "DELETE FROM rep_subs_recalc_month_manual_tbl WHERE id= $p_id_manual;";

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(-1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }
    else
    {
        echo_result(0, 'empty');
    }
   }
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>