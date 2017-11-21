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

$table_name = 'clm_work_pack_header_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    
    $p_id_position = sql_field_val('id_position', 'int');
    $p_idk_work = sql_field_val('idk_work', 'int');
    $p_addr = sql_field_val('addr','record');
    $p_book = sql_field_val('book','string');
    
    $p_dt_work = sql_field_val('dt_work', 'date');
    $p_note = sql_field_val('note', 'string');
    //$p_change_date=sql_field_val('change_date','date');
    $p_id_usr = $session_user;
    
    
    
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "update $table_name set
        id_sector = $p_id_sector, book = $p_book, addr = $p_addr, idk_work = $p_idk_work,
        dt_work = $p_dt_work, id_position = $p_id_position, note = $p_note, id_person = $p_id_usr
        where id = $p_id;"; 

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
      
      $QE = "insert into $table_name ( id, id_sector, book, addr, idk_work, dt_work, id_position, note, id_person)
        values(DEFAULT, $p_id_sector, $p_book, $p_addr, $p_idk_work, $p_dt_work, $p_id_position, $p_note, $p_id_usr) returning id;";    
        
      $res_e = pg_query($Link, $QE);
      
        if (!($res_e)) 
        {
            echo_result(2,pg_last_error($Link));
            pg_query($Link,"rollback");   
            return;
        }    
      
      $row = pg_fetch_array($res_e);
      $id_pack = $row["id"];
        
      $QE = "select clm_work_pack_init_fun($id_pack,$p_id_usr); ";
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
    /*if ($oper == "refresh") {

      $p_dt_change = sql_field_val('change_date', 'date');  
        
      $QE = "select ind_pack_refresh_fun($p_id,$p_dt_change,$p_id_usr); ";
      
      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(-2, 'Data refreshed'.$QE);
      } else {
        echo_result(2, pg_last_error($Link));
      }
    }*/
//------------------------------------------------------    
    
    if ($oper == "del") {

      $QE = "delete from $table_name where id = $p_id;";                 

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


