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

  $oper = $_POST['oper'];
  if ($oper =='edit')
  {
    
  
   if (isset($_POST['json_data'])) {

    $json_str = $_POST['json_data'];
    //echo $json_str;
    $json_str = stripslashes($json_str); 
    //echo $json_str;
    $data_lines = json_decode($json_str,true);
    $p_id_usr = $session_user;
    
    $result=pg_query($Link,"begin");         
    if (!($result)) 
    {
        echo_result(2,pg_last_error($Link));
        return;
    }    
    
    $cnt=0;
    foreach($data_lines as $record) 
    {
      $cnt++;
          
      $id =  $record['id'];
      
      $sum_val=str_replace(',', '.', $record['sum_val']);
      
      $id_grp_lgt = $record['id_grp_lgt'];
      $mmgg_lgt = $record['mmgg_lgt'];
      $demand_val = $record['demand_val'];
      $is_corr = $record['is_corr'];
      
      if ($sum_val=='') $sum_val='null';
      if ($demand_val=='') $demand_val='null';
      if ($is_corr=='') $is_corr='0';

      if ($mmgg_lgt=='') $mmgg_lgt='null';      
      else {
        list($day, $month, $year) = split('[/.-]', $mmgg_lgt);
        
        if(($day!='') && ($month!='')&& ($year!=''))
            {$mmgg_lgt = "'$year-$month-$day'"; }
        else
            {$mmgg_lgt = 'null';}
        }
      
      
      
      $QE = "update acm_dop_lgt_tbl set sum_val = $sum_val , id_grp_lgt = $id_grp_lgt, 
         mmgg_lgt = $mmgg_lgt, dt = Now(), demand_val = $demand_val, is_corr = $is_corr,
        id_person = $p_id_usr where id = $id and flock=0;";  
        
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
  }
    
 if ($oper =='add')
 {
        $id_paccnt = sql_field_val('id_paccnt', 'int');
        $mmgg      = sql_field_val('mmgg', 'date');
        $QE = "insert into acm_dop_lgt_tbl(id, id_paccnt, mmgg, id_person) values (DEFAULT,$id_paccnt,$mmgg,$session_user) returning id;"; 
        
        $res_e = pg_query($Link, $QE);
        if (!($res_e)) 
        {
            echo_result(2,pg_last_error($Link));
            return;
        }    
        else
        {
            $row = pg_fetch_array($res_e);
            $id_new = $row["id"];
        
            echo_result(-1, 'Data ins',$id_new);
            return;
        }
}

 if ($oper =='del')
 {
        $id = sql_field_val('id', 'int');

        $QE = "delete from acm_dop_lgt_tbl where id = $id ;"; 
        
        $res_e = pg_query($Link, $QE);
        if (!($res_e)) 
        {
            echo_result(2,pg_last_error($Link));
            return;
        }    
        else
        {    
            echo_result(-1, 'Data del');
            return;
        }    
        
}
    
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>
