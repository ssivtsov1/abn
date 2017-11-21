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
$table_name = 'acm_pay_load_tbl';

try {
 
  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    $p_old_paccnt = sql_field_val('old_paccnt', 'int');
    
    $p_id_headpay = sql_field_val('id_headpay', 'int');
    $p_value = sql_field_val('summ', 'numeric');
    $p_date = sql_field_val('pdate', 'date');
    $p_date_ob = sql_field_val('date_ob', 'date');
    
    $p_id_usr = $session_user; 
    

//------------------------------------------------------
    if ($oper == "edit") {

    $result=pg_query($Link,"begin");         
    if (!($result)) 
    {
        echo_result(2,pg_last_error($Link));
        return;
    }    
        
      $QE = "UPDATE $table_name  SET id_paccnt=$p_id_paccnt, id_person = $session_user, dt = now()
        WHERE id=$p_id;";
      //echo $QE;
      $result = pg_query($Link, $QE);
      if (!($result)) 
      {
        echo_result(2,pg_last_error($Link));
        pg_query($Link,"rollback");   
        return;
      }    
      
      if ($p_old_paccnt!=$p_id_paccnt)
      {
        if ($p_old_paccnt!='null')
        {    
          $QE = "delete from acm_pay_tbl where id_headpay = $p_id_headpay 
                and id_paccnt = $p_old_paccnt and value = $p_value;";
            
          $result = pg_query($Link, $QE);
      
          if (!($result)) 
          {
            echo_result(2,pg_last_error($Link));
            pg_query($Link,"rollback");   
            return;
          }    
            
        }
        if ($p_id_paccnt!='null')
        {    
          $QE = "insert into acm_pay_tbl (id_headpay, id_person, idk_doc, id_pref, reg_num, 
            reg_date, id_paccnt, value, value_tax, pay_date )
            values($p_id_headpay, $session_user, 100, 10, 0, 
            $p_date_ob, $p_id_paccnt, $p_value, round($p_value/6,2), $p_date ) ;";
      
          $result = pg_query($Link, $QE);
      
          if (!($result)) 
          {
            echo_result(2,pg_last_error($Link));
            pg_query($Link,"rollback");   
            return;
          }    
        
        }
      }
      echo_result(1, 'Data updated');
      pg_query($Link,"commit");   
      
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