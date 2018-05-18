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
 
  if (isset($_POST['id_pack'])) {
      
    $id_pack = $_POST['id_pack'];
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
    $now_time = date('Y-m-d-H-i-s');
    
    $myfile = fopen("../tmp/{$id_pack}_{$now_time}.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $json_str);
    fclose($myfile);    
    
    
    $result=pg_query($Link,"begin");         
    if (!($result)) 
    {
        echo_result(2,pg_last_error($Link));
        return;
    }    
    
    $result = pg_query($Link, "select crt_ttbl();");
    if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        pg_query($Link, "rollback");
        return;
    }

    $result = pg_query($Link, "insert into syi_sysvars_tmp (id, ident,type_ident, value_ident) 
         values(120,'id_person','int', $session_user);");

    if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        pg_query($Link, "rollback");
        return;
    }
    
    
    
    $cnt=0;
    foreach($data_lines as $record) {
      $cnt++;
      $id =  $record['id'];
      
      $indic = $record['indic'];
            
      $dt_indic = $record['dt_indic'];
      $id_operation = $record['id_operation'];
      $indic_real= $record['indic_real'];
      
      //$dt_indic ='';
      
      if ($indic=='') $indic='null';
      if ($indic_real=='') $indic_real='null';
      if ($id_operation=='') $id_operation='null';
      
      if (($dt_indic=='')||($dt_indic=='__.__.____')) $dt_indic='null';
      else {
        list($day, $month, $year) = split('[/.-]', $dt_indic);
        
        if(($day!='') && ($month!='')&& ($year!=''))
            {$dt_indic = "'$year-$month-$day'"; }
        else
            {$dt_indic = 'null';}
        }
 
        
      $QE = "update ind_pack_data set indic = $indic, dt_indic = $dt_indic, 
          id_operation = $id_operation, indic_real = $indic_real , id_person = $p_id_usr, dt_input = now()
          where id_pack = $id_pack and id = $id;";          
        
      $res_e = pg_query($Link, $QE);
      
      if (!($res_e)) 
      {
           echo_result(2,pg_last_error($Link).$QE);
           pg_query($Link,"rollback");              
           return;
       }    
      }
      
     pg_query($Link,"commit");             
     //header("Content-type: application/json;charset=utf-8");
     echo_result(1, 'Data ins',$cnt);
     return;
          
    }    
 
} catch (Exception $e) {

  echo echo_result(2, 'Error: ' . $e->getMessage());
}
?>


