<?php

header("Content-type: application/json;charset=utf-8");

include_once 'abon_en_func.php';
include_once 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();


try {

  //if (isset($_POST['id_pack'])) {
      
   // $id_pack = $_POST['id_pack'];
    $json_str = $_POST['json_data'];
    //echo $json_str;
    $json_str = stripslashes($json_str); 
    //echo $json_str;
    $data_lines = json_decode($json_str,true);
    //$sss = '[{"indic":"1","id":"151"},{"indic":"2","id":"152"},{"indic":"3","id":"153"}]';
    //echo $sss;
    //$data_lines = json_decode($sss,true);
    //var_dump($data_lines);
    $p_id_usr = $session_user;
    
    $result=pg_query($Link,"begin");         
    if (!($result)) 
    {
        echo_result(2,pg_last_error($Link));
        return;
    }    
    
    $cnt=0;
    foreach($data_lines as $record) {
      $cnt++;
      $id =  $record['id'];
      $id_usr =  $record['id_usr'];
      
      $rule = $record['rule'];
      if ($rule=='null') $rule='-1';
 
        
      $QE = "update syi_user_lvl set lvl = $rule
          where id_usr = $id_usr and id_env = $id;";         
        
      $res_e = pg_query($Link, $QE);
      
      if (!($res_e)) 
      {
           pg_query($Link,"rollback");   
           echo_result(2,pg_last_error($Link).$QE);
           return;
       }    
      }
      
     pg_query($Link,"commit");             
     //header("Content-type: application/json;charset=utf-8");
     echo_result(1, 'Data upd',$cnt);
     return;
          
    //}    
 
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


