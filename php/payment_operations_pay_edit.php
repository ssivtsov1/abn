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
$table_name = 'acm_pay_tbl';

$result = pg_query($Link, "select crt_ttbl();");
if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    //pg_query($Link, "rollback");
    return;
}

$result = pg_query($Link, "insert into syi_sysvars_tmp (id, ident,type_ident, value_ident) 
     values(120,'id_person','int', $session_user);");

if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    //pg_query($Link, "rollback");
    return;
}


try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id_doc', 'int');
    $p_id_headpay = sql_field_val('id_headpay', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    $p_reg_num = sql_field_val('reg_num', 'string');
    $p_reg_date = sql_field_val('reg_date', 'date');
    $p_pay_date = sql_field_val('pay_date', 'date');
    
    $p_value = sql_field_val('value', 'numeric');
    $p_value_tax = sql_field_val('value_tax', 'numeric');
    
    $p_mmgg = sql_field_val('mmgg', 'mmgg');
    $p_mmgg_pay = sql_field_val('mmgg_pay', 'mmgg');
    $p_mmgg_hpay = sql_field_val('mmgg_hpay', 'mmgg');
    
    $p_note = sql_field_val('note', 'string');
    $p_idk_doc = sql_field_val('idk_doc', 'int');
    $p_id_usr = $session_user;
    
    
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "update $table_name set
        reg_num = $p_reg_num, reg_date = $p_reg_date, id_paccnt = $p_id_paccnt, pay_date = $p_pay_date,
        value = $p_value,  value_tax = $p_value_tax, 
        mmgg = $p_mmgg, mmgg_pay = $p_mmgg_pay, mmgg_hpay = $p_mmgg_hpay, note = $p_note,
        idk_doc = $p_idk_doc, id_headpay = $p_id_headpay, id_person = $session_user, dt = now()
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

      $QE = "insert into $table_name (id_doc,id_headpay, id_person, idk_doc, id_pref, reg_num, 
            reg_date, id_paccnt, value, value_tax, pay_date, note, mmgg_pay, 
            mmgg_hpay, mmgg)
        values(DEFAULT,$p_id_headpay, $session_user, $p_idk_doc, 10, $p_reg_num, 
            $p_reg_date, $p_id_paccnt, $p_value, $p_value_tax, $p_pay_date,$p_note,
        $p_mmgg_pay, $p_mmgg_hpay, $p_mmgg) returning id_doc;";
        
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


