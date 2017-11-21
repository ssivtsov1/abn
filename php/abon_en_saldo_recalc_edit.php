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

$table_name = 'acm_correct_paccnt';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_dat_b = sql_field_val('mmgg_begin', 'mmgg');
    $p_dat_e = sql_field_val('mmgg_end', 'mmgg');
    
    $p_id_usr = $session_user;    
    
//------------------------------------------------------
    if ($oper == "add") {

      $QE = " insert into acm_correct_paccnt(mmgg_corr,id_paccnt,id_person,is_calc) 
        select distinct date_trunc('month', c_date)::date , $p_id_paccnt , $session_user, 0
            from calendar
            where c_date<= $p_dat_e
            and   c_date>= $p_dat_b
            order by date_trunc('month', c_date)::date;";
        
      $res_e = pg_query($Link, $QE);
      
        if (!($res_e)) 
        {
            echo_result(2,pg_last_error($Link));
            return;
        }    
      
     // $row = pg_fetch_array($res_e);
     // $id_doc = $row["id_doc"];
        
      if ($res_e){
        echo_result(1, 'Data ins');
      }
      else{
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


