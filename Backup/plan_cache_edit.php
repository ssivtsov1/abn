<?php

header('Content-type: text/html; charset=utf-8');

include_once 'abon_en_func.php';
include_once 'abon_ded_func.php';


session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];
$p_mmgg = trim(sql_field_val('dt_b', 'mmgg'));
 
    $year_n = substr($p_mmgg,1,4);
    $month_n = substr($p_mmgg,6,2);
session_write_close();
$table_name = 'act_plan_cache_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];
 
    //$p_id = sql_field_val('se', 'int');
    $p_id=$_POST['id']; 
    $p_days=$_POST['days']; 
    $p_sector=$_POST['sector']; 
    $p_mmgg=$_POST['date']; 
    $p_cntr="'".$_POST['cntr']."'"; 
    $year_n = substr($p_mmgg,6,4);
    $month_n = substr($p_mmgg,3,2);
    //$year_n = substr($p_mmgg,1,4);
    //$month_n = substr($p_mmgg,6,2);
    //$p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_id_usr = $session_user;
    
//    $year_n = 2018;
//    $month_n = 3;
//------------------------------------------------------
    if ($oper == "add") {

      $QE = "insert into act_plan_cache_tbl (year,month,sector,days,id_user,cntr) 
        values($year_n, $month_n, "."'".$p_sector."'".", $p_days, $session_user, $p_cntr ) ;";
      
//      $f=fopen('aaa_data.dat','w+');
//      fputs($f,$QE);
      
     
      $res_e = pg_query($Link, $QE);
      
//      $QE = "update ind_pack_header t
//             set id_runner=p.id
//             from 
//             prs_runner_sectors as s, act_plan_cache_tbl as p4, prs_persons as p
//             where s.id = t.id_sector
//             and trim(s.name) = trim(p4.sector)
//             and trim(p.represent_name) = trim(p4.cntr)
//             and p4.year=$year_n and p4.month=$month_n
//             and trim(s.name)="."'".$p_sector."'" . 
//              " and trim(p4.cntr)=".$p_cntr.
//              " and t.work_period="."'".$p_mmgg."'";
//      
//      $f=fopen('aaa_data1.dat','w+');
//      fputs($f,$QE);
//      
//      $res_e = pg_query($Link, $QE);
      
        if (!($res_e)) 
        {
            echo_result(2,pg_last_error($Link));
            return;
        }    
        
      if ($res_e){
        echo_result(-1, 'Data ins',$id);
      }
      else{
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    if ($oper == "del") {

      $QE = "delete from act_plan_cache_tbl where sector = '$p_id' and cntr = $p_cntr ".
              " and year=".$year_n." and month=".$month_n;                 

      $f=fopen('aaa_data.dat','w+');
      fputs($f,$QE);
      //file_put_contents('aaa_data.dat', $_POST);
      
      $res_e = pg_query($Link, $QE);
      
//      $QE = "update ind_pack_header t
//             set id_runner=0
//             from 
//             prs_runner_sectors as s, act_plan_cache_tbl as p4, prs_persons as p
//             where s.id = t.id_sector
//             and trim(s.name) = trim(p4.sector)
//             and trim(p.represent_name) = trim(p4.cntr)
//             and p4.year=$year_n and p4.month=$month_n
//             and trim(s.name)="."'".$p_sector."'" . " and t.work_period="."'".$p_mmgg."'";
//      
//       $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(-1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }
//------------------------------------------------------
    if ($oper == "delall") {

      $QE = "delete from act_plan_cache_tbl where cntr = $p_cntr  and year=".$year_n." and month=".$month_n;
     
      $f=fopen('aaa_data.dat','w+');
      fputs($f,$QE);

      $res_e = pg_query($Link, $QE);
      
//     $QE = "update ind_pack_header t
//             set id_runner=0
//             from 
//             prs_runner_sectors as s, act_plan_cache_tbl as p4, prs_persons as p
//             where s.id = t.id_sector
//             and trim(s.name) = trim(p4.sector)
//             and trim(p.represent_name) = trim(p4.cntr)
//             and p4.year=$year_n and p4.month=$month_n
//             and t.work_period="."'".$p_mmgg."'";
//      
//       $res_e = pg_query($Link, $QE);


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


