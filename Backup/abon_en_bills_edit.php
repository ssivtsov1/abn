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
$table_name = 'acm_bill_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id_doc', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');

    $p_reg_num = sql_field_val('reg_num', 'string');
    $p_reg_date = sql_field_val('reg_date', 'date');
        
    $p_value = sql_field_val('value', 'numeric');
    $p_value_tax = sql_field_val('value_tax', 'numeric');
    $p_demand = sql_field_val('demand', 'int');
    
    $p_mmgg = sql_field_val('mmgg', 'mmgg');
    $p_mmgg_bill = sql_field_val('mmgg_bill', 'mmgg');
    

    $p_idk_doc = sql_field_val('idk_doc', 'int');
    $p_id_usr = $session_user;
    
    
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "update $table_name set
        reg_num = $p_reg_num, reg_date = $p_reg_date, id_paccnt = $p_id_paccnt,
        value = $p_value,  value_tax = $p_value_tax, demand = coalesce( $p_demand ,0), 
        mmgg = $p_mmgg, mmgg_bill = $p_mmgg_bill,  idk_doc = $p_idk_doc
        where id_doc = $p_id;"; 

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(1, 'Data updated');
      } else {
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    if ($oper == "add") {

      $QE = "insert into $table_name (id_doc, id_person, idk_doc, id_pref, reg_num, 
            reg_date, id_paccnt, value, value_tax, demand, mmgg_bill, mmgg)
        values(DEFAULT, $session_user, $p_idk_doc, 10, $p_reg_num, 
            $p_reg_date, $p_id_paccnt, $p_value, $p_value_tax, coalesce( $p_demand ,0), 
        $p_mmgg_bill, $p_mmgg) returning id_doc;";
        
      $res_e = pg_query($Link, $QE);
      
        if (!($res_e)) 
        {
            echo_result(2,pg_last_error($Link));
            return;
        }    
      
      $row = pg_fetch_array($res_e);
      $id_doc = $row["id_doc"];
        
      if ($res_e){
        echo_result(1, 'Data ins',$id_doc);
      }
      else{
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    if ($oper == "del") {

      $QE = "delete from $table_name where id_doc = $p_id;";                 

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


