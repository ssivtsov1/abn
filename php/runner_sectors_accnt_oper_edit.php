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

$table_name = 'prs_runner_paccnt';

try {

  if (isset($_POST['oper'])) {
      
    if ((isset($_POST['id_array']))||(isset($_POST['id'])))
    {
        $p_id_array = $_POST['id_array'];
        $p_id = sql_field_val('id', 'int');
        $oper = $_POST['oper'];
    }
    
    //echo_result(2,$p_id_array);
    
    
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_dt_b = sql_field_val('change_date', 'date');
    
    $p_id_usr = $session_user;
    
//------------------------------------------------------ 
    if ($oper == "add") {

      foreach ($p_id_array as $vid) {        

          $QE = "select prs_runner_paccnt_add_fun($p_id_sector,$vid,$p_dt_b, $p_id_usr );";
          $res_e = pg_query($Link, $QE);
      }
      
      if ($res_e)
        echo_result(1, 'Data ins');
      else
        echo_result(2, pg_last_error($Link));
    }
//------------------------------------------------------
    if ($oper == "del") {

     foreach ($p_id_array as $vid) {                

      $QE = "select prs_runner_paccnt_del_fun(null,$vid,$p_dt_b, $p_id_usr);";
      $res_e = pg_query($Link, $QE);
     }

      if ($res_e)
        echo_result(1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }
//------------------------------------------------------
    if ($oper == "del_one") {

      
      $QE = "select prs_runner_paccnt_del_fun($p_id,null,$p_dt_b, $p_id_usr);";
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


