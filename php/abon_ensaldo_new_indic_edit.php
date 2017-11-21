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

$result = pg_query($Link, "select crt_ttbl();");
if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    return;
}

$result = pg_query($Link, "insert into syi_sysvars_tmp (id, ident,type_ident, value_ident) 
     values(120,'id_person','int', $session_user);");

if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    return;
}

$p_id_reason = sql_field_val('reason', 'int');

$result = pg_query($Link, "insert into syi_sysvars_tmp (id, ident,type_ident, value_ident) 
     values(120,'id_edit_reason','int', $p_id_reason);");

if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    return;
}


try {
    if (isset($_POST['oper'])) {
        $oper = $_POST['oper'];
    }

  if ($oper=='add')  
  {
    
  if (isset($_POST['json_data'])) {
      
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
      $id_paccnt =  $record['id_paccnt'];
      $id_meter =  $record['id_meter'];
      $id_typemet =  $record['id_type_meter'];
      $carry =  $record['carry'];
      $coef_comp =  $record['k_tr'];
      $id_zone =  $record['id_zone'];
      
      $num_m = $record['num_meter'];
      $num_eqp = "'$num_m'";
      
      //$num_eqp =  $record['num_meter'];
      $id_energy =  10;
      $id_operation =  $record['id_operation'];
      
      $indic = $record['indic']; 
      
      $indic_real = $record['indic_real']; 
      if ($indic_real=='') $indic_real='null';
      
      $dt_indic = $record['dt_indic'];
      $mmgg = $record['mmgg'];
      //$dt_indic ='';
      
      if ($indic=='') $indic='null';
      
      if ($dt_indic=='') $dt_indic='null';
      else {
        list($day, $month, $year) = split('[/.-]', $dt_indic);
        
        if((($day!='') && ($month!='')&& ($year!=''))&&(strlen($year)==4))
            {$dt_indic = "'$year-$month-$day'"; }
        else
            {$dt_indic = 'null';}
        }

      if ($mmgg=='') $mmgg='null';
      else {
        list($day, $month, $year) = split('[/.-]', $mmgg);
        
        if((($day!='') && ($month!='')&& ($year!=''))&&(strlen($year)==4))
            {$mmgg = "'$year-$month-01'"; }
        else
            {$mmgg = 'null';}
        }
        
        
      $QE = " insert into acm_indication_tbl(id_paccnt, id_meter, id_typemet, 
              carry, coef_comp, id_zone, num_eqp, id_energy, value, dat_ind,id_operation, mmgg, id_person, indic_real)
       values($id_paccnt, $id_meter, $id_typemet, 
              $carry, $coef_comp, $id_zone, $num_eqp, $id_energy, $indic, $dt_indic, $id_operation, $mmgg,$session_user, $indic_real ) ;";         
        
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

  if ($oper=='edit')  
  {
    
  if (isset($_POST['json_data'])) {
      
    $json_str = $_POST['json_data'];

    $json_str = stripslashes($json_str); 

    $data_lines = json_decode($json_str,true);

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

      $id_operation =  $record['id_operation'];
      $indic = $record['indic'];
      $dt_indic = $record['dt_indic'];
      $id_zone = $record['id_zone'];
      $mmgg = $record['mmgg'];
      
      if ($indic=='') $indic='null';
      
      $indic_real = $record['indic_real']; 
      if ($indic_real=='') $indic_real='null';
      
      if ($dt_indic=='') $dt_indic='null';
      else {
        list($day, $month, $year) = split('[/.-]', $dt_indic);
        
        if((($day!='') && ($month!='')&& ($year!=''))&&(strlen($year)==4))
            {$dt_indic = "'$year-$month-$day'"; }
        else
            {$dt_indic = 'null';}
        }

      if ($mmgg=='') $mmgg='null';
      else {
        list($day, $month, $year) = split('[/.-]', $mmgg);
        
        if((($day!='') && ($month!='')&& ($year!=''))&&(strlen($year)==4))
            {$mmgg = "'$year-$month-01'"; }
        else
            {$mmgg = 'null';}
        }
        
      $QE = " select coalesce(id_ind,0) as id_ind,coalesce(id_work,0) as id_work
       from acm_indication_tbl where id = $id ;";         
      $res_e = pg_query($Link, $QE);
      
      if (!($res_e)) 
      {
        echo_result(2,pg_last_error($Link).$QE);
        return;
      }

      $row = pg_fetch_array($res_e);
      $id_ind = $row['id_ind'];
      $id_work = $row['id_work'];
        
      $QE_update = " update acm_indication_tbl set value = $indic, dat_ind = $dt_indic ,id_operation = $id_operation,
       id_zone = $id_zone, mmgg = $mmgg , indic_real = $indic_real , id_person = $session_user, dt = now() where id = $id ;";
        
      
      if ($id_ind!=0)
      {
          
        if ($indic_real=='null')
        {
         $QE = " update ind_pack_data set indic = $indic, dt_indic = $dt_indic , id_zone = $id_zone,
           id_person = $session_user , dt_input = now() where id = $id_ind ;";
        }
        else
        {
         $QE = " update ind_pack_data set indic = $indic, dt_indic = $dt_indic , id_zone = $id_zone,
           id_person = $session_user , dt_input = now() , indic_real = $indic_real  where id = $id_ind ;";
        }
        
        $res_e = pg_query($Link, $QE);
      
        if (!($res_e)) 
        {
          echo_result(2,pg_last_error($Link).$QE);
          return;
        }
      }
      
      if ($id_work!=0)
      {
        $QE_update = " update acm_indication_tbl set value = $indic, id_zone = $id_zone, mmgg = $mmgg,
          id_person = $session_user, indic_real = $indic_real,  dt = now()   where id = $id ;";         

        if ($indic_real=='null')
            $QE = " update clm_work_indications_tbl set indic = $indic, id_zone = $id_zone  where id = $id_work ;";
        else
            $QE = " update clm_work_indications_tbl set indic = $indic, id_zone = $id_zone, indic_real = $indic_real  where id = $id_work ;";
            
        $res_e = pg_query($Link, $QE);
      
        if (!($res_e)) 
        {
          echo_result(2,pg_last_error($Link).$QE);
          return;
        }
      }
      
      $res_e = pg_query($Link, $QE_update);
      
      if (!($res_e)) 
      {
           echo_result(2,pg_last_error($Link).$QE);
           pg_query($Link,"rollback");   
           return;
      }    

     }
     pg_query($Link,"commit");             
     //header("Content-type: application/json;charset=utf-8");
     echo_result(1, 'Data upd',$cnt);
     return;
          
    }    
  }
  
  if ($oper=='del')  
  {
      $id = $_POST['id'];

      $QE = " select coalesce(id_ind,0) as id_ind,coalesce(id_work,0) as id_work
       from acm_indication_tbl where id = $id ;";         
      $res_e = pg_query($Link, $QE);
      
      if (!($res_e)) 
      {
        echo_result(2,pg_last_error($Link).$QE);
        return;
      }

      $row = pg_fetch_array($res_e);
      $id_ind = $row['id_ind'];
      $id_work = $row['id_work'];
      
      $QE = " delete from acm_indication_tbl where id = $id ;";         
        
      $res_e = pg_query($Link, $QE);
      
      if (!($res_e)) 
      {
        echo_result(2,pg_last_error($Link).$QE);
        return;
      }
      
      if ($id_ind!=0)
      {
        $QE = " update ind_pack_data set indic = null, dt_indic = null , id_operation = null, indic_real = null,
          id_person = $session_user , dt_input = now()  where id = $id_ind ;";         
        
        $res_e = pg_query($Link, $QE);
      
        if (!($res_e)) 
        {
          echo_result(2,pg_last_error($Link).$QE);
          return;
        }
      }
      
      if ($id_work!=0)
      {
        $QE = " update clm_work_indications_tbl set indic = null where id = $id_work ;";         
        
        $res_e = pg_query($Link, $QE);
      
        if (!($res_e)) 
        {
          echo_result(2,pg_last_error($Link).$QE);
          return;
        }
      }
      
      echo_result(1, 'Data del',$cnt);
      return;
      
  }  
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


