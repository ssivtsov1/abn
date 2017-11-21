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

$table_name = 'clm_switching_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_dt_action = sql_field_val('dt_action', 'date');
    $p_dt_warning = sql_field_val('dt_warning', 'date');
    $p_dt_create = sql_field_val('dt_create', 'date');
    $p_dt_sum = sql_field_val('dt_sum', 'date');
    $p_mmgg_debet = sql_field_val('mmgg_debet', 'mmgg'); 
    $p_action = sql_field_val('action', 'int');
    $p_sum_warning = sql_field_val('sum_warning', 'numeric');
    $p_demand_varning = sql_field_val('demand_varning', 'int');
    $p_comment = sql_field_val('comment', 'string');
    $p_act_num = sql_field_val('act_num', 'string'); 
    $p_id_position = sql_field_val('id_position', 'int');
    $p_id_switch_place = sql_field_val('id_switch_place', 'int');
    
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET dt_action=$p_dt_action, dt_warning=$p_dt_warning, action=$p_action, comment=$p_comment,
        dt_create = $p_dt_create, dt_sum = $p_dt_sum, sum_warning = $p_sum_warning ,
        id_person = $session_user, id_paccnt = $p_id_paccnt ,
        id_switch_place = $p_id_switch_place, id_position = $p_id_position, act_num = $p_act_num, 
        demand_varning = $p_demand_varning, mmgg_debet = $p_mmgg_debet
        WHERE id=$p_id;";

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(1, 'Data updated');
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
        
   $QE = "INSERT INTO $table_name (id_paccnt, dt_action, dt_warning, action, comment, 
        dt_create,dt_sum,sum_warning,id_person, id_switch_place,id_position,act_num, 
        demand_varning, mmgg_debet )
      VALUES($p_id_paccnt, $p_dt_action, $p_dt_warning, $p_action, $p_comment, 
        $p_dt_create , $p_dt_sum , $p_sum_warning ,$session_user, 
      $p_id_switch_place,$p_id_position,$p_act_num, $p_demand_varning, $p_mmgg_debet )";

      $res_e = pg_query($Link, $QE);
      if (!($res_e))
      {
        echo_result(2, pg_last_error($Link));
        pg_query($Link,"rollback");   
        return;

      }

   if (($p_action==1)||($p_action==3))
   {
     $QE="select clt_change_fun(32,$p_id_paccnt,null,$p_dt_action,$session_user,1,0)";
 
     $result=pg_query($Link,$QE);
     if (!($result)) 
     {
       echo_result(2,pg_last_error($Link));
       pg_query($Link,"rollback");   
       return;
     }    
      
     if ($p_action ==1 )
       $QE="UPDATE clm_paccnt_tbl   SET activ=false  WHERE id = $p_id_paccnt;";

     if ($p_action ==3 )
       $QE="UPDATE clm_paccnt_tbl   SET activ=true  WHERE id = $p_id_paccnt;";
     
    $res_e=pg_query($Link,$QE);
     if ($res_e) {
         echo_result(-1, 'Data ins');
         pg_query($Link,"commit");   
        }
     else        {
        echo_result(2,pg_last_error($Link));
        pg_query($Link,"rollback");   
     }
    }  
    else
    {
         echo_result(-1, 'Data ins');
         pg_query($Link,"commit");   
    }

 }
//------------------------------------------------------
    if ($oper == "del") {

      $QE = "DELETE FROM $table_name WHERE id= $p_id;";

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(-1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }
//------------------------------------------------------
    if ($oper == "del_warning") {

      $QE = "DELETE FROM $table_name WHERE action = 2 and mmgg = (Select mmgg from $table_name where id = $p_id );";

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