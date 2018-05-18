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
$table_name = 'rep_spravka_queue_tmp';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];
 
    $p_id = sql_field_val('id', 'int');
    //$p_id_paccnt = sql_field_val('id_paccnt', 'int');

    $p_id_usr = $session_user;
    
    
//------------------------------------------------------
    if ($oper == "add") {


        $p_mmgg = sql_field_val('dt_sp', 'mmgg');
    
        $p_dt_b = sql_field_val('dt_b', 'mmgg');
        $p_dt_e = sql_field_val('dt_e', 'mmgg');
    

        $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
        $p_num_sp = sql_field_val('num_sp', 'int');
        $p_dt_sp = sql_field_val('dt_sp', 'date');
        

        $p_num_input = sql_field_val('num_input', 'string');
        $p_dt_input = sql_field_val('dt_input', 'date');
        
        $p_people_count = sql_field_val('people_count', 'int');
    
    
        $p_heat_area = sql_field_val('heat_area', 'numeric');
    
        $p_id_person = sql_field_val('id_person', 'int');

   
        $p_hotw = sql_field_val('hotw', 'int');
        $p_hotw_gas = sql_field_val('hotw_gas', 'int');
        $p_coldw = sql_field_val('coldw', 'int');
        $p_plita = sql_field_val('plita', 'int');
        $p_show_dte = sql_field_val('show_dte', 'int');
    
        $p_social_norm = sql_field_val('social_norm', 'int');
    
        $p_protocol = sql_field_val('write_protocol', 'int');        
        
        $rows_count=0;
        
        $SQL = " select id from rep_spravka_queue_tmp 
          where id_paccnt = $p_id_paccnt and dt_sp = $p_dt_sp 
          and  date_start = $p_dt_b and date_end = $p_dt_e and id_user = $session_user ;";
            
        $result = pg_query($Link, $SQL);
        if (!($result)) 
        {
            echo_result(2,pg_last_error($Link));
            return;
        }    
        
        $rows_count = pg_num_rows($result);
        
        if ($rows_count==0)
        {
          $SQL = " INSERT INTO rep_spravka_queue_tmp(
            id_paccnt, doc_type, num_sp, dt_sp, date_start, date_end, 
            num_input, dt_input, people_count, heat_area, hotw, hotw_gas, 
            coldw, plita, social_norm, id_person, id_user)          
            values ($p_id_paccnt, null, $p_num_sp, $p_dt_sp,$p_dt_b,$p_dt_e,
            $p_num_input, $p_dt_input, $p_people_count, $p_heat_area, 
            $p_hotw, $p_hotw_gas,$p_coldw, $p_plita, $p_social_norm, $p_id_person, $session_user ); ";        
        
        
          $res_e = pg_query($Link, $SQL);
      
        
          if ($res_e){
            echo_result(-1, 'Data ins',$id_doc);
          }
          else{
            echo_result(2, pg_last_error($Link));
          }
      }
      else {
        echo_result(0, 'record already exists');          
      }
    }
//------------------------------------------------------
    if ($oper == "del") {

      $QE = "delete from rep_spravka_queue_tmp where id = $p_id and id_user = $session_user ;";                 

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(-1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }
//------------------------------------------------------
    if ($oper == "delall") {

      $QE = "delete from rep_spravka_queue_tmp where id_user = $session_user ;";

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(-1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }    
//------------------------------------------------------    
    if ($oper == "set_date") {

      $p_dt_b = sql_field_val('dt_b', 'mmgg');
      $p_dt_e = sql_field_val('dt_e', 'mmgg');
      $p_dt_sp = sql_field_val('dt_sp', 'date');
        
        
      $QE = "update rep_spravka_queue_tmp set 
      dt_sp = $p_dt_sp, date_start= $p_dt_b, date_end=$p_dt_e
      where id_user = $session_user ;";                 

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(-1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }
//------------------------------------------------------
    
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


