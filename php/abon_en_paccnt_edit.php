<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();
try {

    
if (isset($_POST['submitButton'])) 
{    
  $oper=$_POST['submitButton'];
}
else
{    
    if (isset($_POST['oper'])) {
        $oper=$_POST['oper'];
    }
}
    
//throw new Exception(json_encode($_POST));

//------------------------------------------------------
if (($oper=="edit")||($oper=="arch")||($oper=="unarch")) {

$p_book = sql_field_val('book','string');
$p_code = sql_field_val('code','string');
$p_addr = sql_field_val('addr','record');
$p_id_abon = sql_field_val('id_abon','int');
$p_id_gtar = sql_field_val('id_gtar','int');
$p_id_cntrl =sql_field_val('id_cntrl','int');
$p_idk_house =sql_field_val('idk_house','int');
$p_id_dep = sql_field_val('id_dep','int');

$p_note = sql_field_val('note','string');
$p_n_subs = sql_field_val('n_subs','string');
$p_subs_name = sql_field_val('subs_name','string');

$p_activ = sql_field_val('activ','bool');
$p_rem_worker = sql_field_val('rem_worker','bool');
$p_not_live = sql_field_val('not_live','bool');
//$p_r_house = sql_field_val('r_house','bool');
$p_pers_cntrl = sql_field_val('pers_cntrl','bool');
$p_green_tarif = sql_field_val('green_tarif','bool');
$p_heat_area = sql_field_val('heat_area','numeric');

$p_archive = sql_field_val('archive','int');

$p_id_sector = sql_field_val('id_sector','int');

$p_recalc_subs = sql_field_val('recalc_subs','int');

$p_dt_b = sql_field_val('dt_b','date');
$p_dt_dod = sql_field_val('dt_dod','date');
$p_change_date=sql_field_val('change_date','date');
$p_id_usr = $session_user;

$p_id=sql_field_val('id','int');    

$result=pg_query($Link,"begin");
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   return;
 }    

 $QE="select clt_change_fun(32,$p_id,$p_id_abon,$p_change_date,$p_id_usr,1,0)";
 
 //echo $QE;
 
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    

$QE="UPDATE clm_paccnt_tbl
   SET  book=$p_book, code=$p_code, addr=$p_addr, id_abon=$p_id_abon, 
        id_gtar = $p_id_gtar, id_cntrl=$p_id_cntrl, n_subs= $p_n_subs,
        activ=$p_activ, rem_worker=$p_rem_worker, not_live= $p_not_live,
        idk_house=$p_idk_house, pers_cntrl = $p_pers_cntrl, heat_area = $p_heat_area,  
        dt_dod = $p_dt_dod, green_tarif = $p_green_tarif, recalc_subs = $p_recalc_subs, 
        note=$p_note, dt_b = $p_dt_b , archive = $p_archive, id_dep = $p_id_dep
 WHERE id = $p_id;";

 //echo $QE;
 $res_e=pg_query($Link,$QE);
 
 if (!($res_e)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    

 
 if ($p_id_sector!='null')
 {
     
     
     $QE="select id_sector from prs_runner_paccnt where id_paccnt = $p_id";
     $res_e=pg_query($Link,$QE);
 
     if (!($res_e)) 
     {
       echo_result(2,pg_last_error($Link));
       pg_query($Link,"rollback");   
       return;
     }    
     $count = pg_num_rows($res_e);
     
     
     if ($count>0)
     {
         $row = pg_fetch_array($res_e);
         $old_sector = $row['id_sector'];
     
        if ($old_sector !=$p_id_sector)
        {
         

            $QE = "select prs_runner_paccnt_add_fun($p_id_sector,$p_id,$p_change_date, $p_id_usr );";
            $res_e = pg_query($Link, $QE);
     
            if (!($res_e)) 
            {
                echo_result(2,pg_last_error($Link));
                pg_query($Link,"rollback");   
                return;
            }    
            //throw new Exception(json_encode($QE));                 
        }
         
     }
     else 
     {

        $QE = "select prs_runner_paccnt_add_fun($p_id_sector,$p_id,$p_change_date, $p_id_usr );";
        $res_e = pg_query($Link, $QE);
     
        if (!($res_e)) 
        {
            echo_result(2,pg_last_error($Link));
            pg_query($Link,"rollback");   
            return;
        }    
         
     }

 } 
 
 if ($p_subs_name!='null')
 {
    $QE = "select id from  cli_ext_params_tbl where id_param = 1 and id_paccnt = $p_id;";
    $res_e = pg_query($Link, $QE);
     
       if (!($res_e)) {
       echo_result(2, pg_last_error($Link));
       pg_query($Link, "rollback");
       return;
    }
    $count = pg_num_rows($res_e);
     
    
    if ($count>0)
    {
         $row = pg_fetch_array($res_e);
         $current_param = $row['id'];
         
         $QE = "update cli_ext_params_tbl set param_value = $p_subs_name where id_paccnt = $p_id and id_param = 1;";
         $res_e = pg_query($Link, $QE);
           if (!($res_e)) {
           echo_result(2, pg_last_error($Link));
           pg_query($Link, "rollback");
           return;
        }        
         
    }    
    else
    {
        
      $QE = "insert into cli_ext_params_tbl (id_paccnt,id_param,param_value) values( $p_id ,1, $p_subs_name );";
      $res_e = pg_query($Link, $QE);
     
         if (!($res_e)) {
         echo_result(2, pg_last_error($Link));
         pg_query($Link, "rollback");
         return;
      }        
    }
     
 }
 else
 {
    $QE = "delete from cli_ext_params_tbl where id_param = 1 and id_paccnt = $p_id;";
    $res_e = pg_query($Link, $QE);
     
       if (!($res_e)) {
       echo_result(2, pg_last_error($Link));
       pg_query($Link, "rollback");
       return;
    }
 }
 
 echo_result(1,'Data updated.');
 pg_query($Link,"commit");   
 
 
}
//------------------------------------------------------
if ($oper=="add") {

$p_book = sql_field_val('book','string');
$p_code = sql_field_val('code','string');
$p_addr = sql_field_val('addr','record');
$p_id_abon = sql_field_val('id_abon','int');
$p_id_gtar = sql_field_val('id_gtar','int');
$p_id_cntrl =sql_field_val('id_cntrl','int');
$p_idk_house =sql_field_val('idk_house','int');
$p_id_dep = sql_field_val('id_dep','int');
$p_note = sql_field_val('note','string');
$p_n_subs = sql_field_val('n_subs','string');
$p_subs_name = sql_field_val('subs_name','string');

$p_activ = sql_field_val('activ','bool');
$p_rem_worker = sql_field_val('rem_worker','bool');
$p_not_live = sql_field_val('not_live','bool');
//$p_r_house = sql_field_val('r_house','bool');
$p_pers_cntrl = sql_field_val('pers_cntrl','bool');
$p_green_tarif = sql_field_val('green_tarif','bool');
$p_heat_area = sql_field_val('heat_area','numeric');
$p_recalc_subs = sql_field_val('recalc_subs','int');
$p_id_sector = sql_field_val('id_sector','int');

$p_dt_b = sql_field_val('dt_b','date');

$p_dt_dod = sql_field_val('dt_dod','date');
$p_id_usr = $session_user;

$result=pg_query($Link,"begin");
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   return;
 }    

 $QE="select clt_change_fun(31,null,$p_id_abon,$p_dt_b,$p_id_usr,1,0)";
 
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    


$QE="INSERT INTO clm_paccnt_tbl(
            id,id_dep, book, code, addr, id_abon, id_gtar, id_cntrl, n_subs, dt_dod,
            activ, rem_worker, not_live, idk_house, pers_cntrl,green_tarif, heat_area, note, recalc_subs, dt_b,id_person )
 values( DEFAULT,$p_id_dep, $p_book, $p_code, $p_addr, $p_id_abon, $p_id_gtar, $p_id_cntrl, $p_n_subs, $p_dt_dod,
        $p_activ, $p_rem_worker, $p_not_live, $p_idk_house, $p_pers_cntrl,$p_green_tarif,$p_heat_area,$p_note,$p_recalc_subs, $p_dt_b, $p_id_usr ) returning id; "; 

 $res_e=pg_query($Link,$QE);

 
 if (!($res_e)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    
 
 $row = pg_fetch_array($res_e);
 $new_id = $row['id'];
 
 if ($p_id_sector!='null')
 {
     
    $QE = "select prs_runner_paccnt_add_fun($p_id_sector,$new_id,$p_dt_b, $p_id_usr );";
    $res_e = pg_query($Link, $QE);
     
    if (!($res_e)) 
    {
      echo_result(2,pg_last_error($Link));
      pg_query($Link,"rollback");   
      return;
    }    
     
 }
 if ($p_subs_name!='null')
 {
      $QE = "insert into cli_ext_params_tbl (id_paccnt,id_param,param_value) values( $new_id ,1, $p_subs_name );";
      $res_e = pg_query($Link, $QE);
     
         if (!($res_e)) {
         echo_result(2, pg_last_error($Link));
         pg_query($Link, "rollback");
         return;
      }        

 }
 
 
 pg_query($Link,"commit");        
 echo_result(-1,'Data ins',$new_id);

 }
//------------------------------------------------------
if ($oper=="del") {

 $p_id=sql_field_val('id','int');    
 $p_id_abon = sql_field_val('id_abon','int');
 $p_change_date=sql_field_val('change_date','date');
 $p_id_usr = $session_user;
 
 $result=pg_query($Link,"begin");
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   return;
 }    

 $QE="select clt_change_fun(33,$p_id,$p_id_abon,$p_change_date,$p_id_usr,1,0)";
 
 $result=pg_query($Link,$QE);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   pg_query($Link,"rollback");   
   return;
 }    

 
 $QE="Delete from clm_paccnt_tbl where id= $p_id;"; 

 $res_e=pg_query($Link,$QE);

 if ($res_e) {
     echo_result(-2,'Data delated');
     pg_query($Link,"commit");   
     }
 else {
     echo_result(2,pg_last_error($Link));
     pg_query($Link,"rollback");   
 }
}


}
catch (Exception $e) {

 echo echo_result(1,'Error: '.$e->getMessage());
}

?>