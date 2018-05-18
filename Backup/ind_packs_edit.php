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

$table_name = 'ind_pack_header';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id_pack', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_runner = sql_field_val('id_runner', 'int');
    $p_id_ioper = sql_field_val('id_ioper', 'int');
   
    $p_num_pack = sql_field_val('num_pack', 'string');
    $p_dt_pack = sql_field_val('dt_pack', 'date');
    
    $p_change_date=sql_field_val('change_date','date');
    $p_id_usr = $session_user;
    
    
    
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "update $table_name set
        id_sector = $p_id_sector, id_runner = $p_id_runner, num_pack = $p_num_pack, dt_pack = $p_dt_pack,
        id_ioper = $p_id_ioper, id_person = $p_id_usr
        where id_pack = $p_id;"; 

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(1, 'Data updated'.$QE);
      } else {
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    if ($oper == "add") {

      $result=pg_query($Link,"begin");         
       if (!($result)) 
        {
            echo_result(2,pg_last_error($Link));
            return;
        }    
      
      $QE = "insert into $table_name ( id_pack, id_sector, id_runner, num_pack, dt_pack, id_ioper, id_person)
        values(DEFAULT, $p_id_sector, $p_id_runner, $p_num_pack, $p_dt_pack, $p_id_ioper, $p_id_usr) returning id_pack;";         
        
      $res_e = pg_query($Link, $QE);
      
        if (!($res_e)) 
        {
            pg_query($Link,"rollback");   
            echo_result(2,pg_last_error($Link));
            return;
        }    
      
      $row = pg_fetch_array($res_e);
      $id_pack = $row["id_pack"];
        
      $QE = "select ind_pack_init_fun($id_pack,$p_dt_pack,$p_id_usr); ";
      $res_e = pg_query($Link, $QE);
      
      if ($res_e){
        pg_query($Link,"commit");             
        echo_result(-1, 'Data ins');
      }
      else{
        echo_result(2, pg_last_error($Link).$QE);
        pg_query($Link,"rollback");             
      }
    }
//------------------------------------------------------
    if ($oper == "refresh") {

      $p_dt_change = sql_field_val('change_date', 'date');  
        
      $QE = "select ind_pack_refresh_fun($p_id,$p_dt_change,$p_id_usr); ";
      
      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(-2, 'Data refreshed '.$QE);
      } else {
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------    
    
    if ($oper == "del") {

      $QE = "delete from $table_name where id_pack = $p_id;";                 

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(-1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


