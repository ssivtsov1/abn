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

try {

  if (isset($_POST['oper'])) {

    $id_desn = 39824; 
    $id_novoz = 39825;
    $id_cherr = 43351;
      
    $id_cher_res = 41696;
    $id_cher_slav = 14633;
    $id_cher_nowoz =39822;
    
    $id_neg_m = 39826;
    $id_neg_s = 40854;

    $id_pril_m = 39827;
    $id_pril_s = 41141;
    
    $id_krp = 40529;
    $id_brz = 40016;
    $id_sosn = 41527;
    $id_krk = 40624;
    $id_mena = 40771;
    
    $oper = $_POST['oper'];
    $region = '';
    $where = '';
    
    if (isset($_POST['submitButton'])) 
    {    
        $region=$_POST['submitButton'];
        
        $id_region = 'null';
        
        if ($region=='desn') $id_region = $id_desn;
        if ($region=='nowoz') $id_region = $id_novoz;
        if ($region=='cher') $id_region = $id_cherr;
        
        if ($region=='neg_m') $id_region = $id_neg_m;
        if ($region=='neg_s') $id_region = $id_neg_s;

        if ($region=='pril_m') $id_region = $id_pril_m;
        if ($region=='pril_s') $id_region = $id_pril_s;
        
        if ($region=='cher_res')   $id_region = $id_cher_res;
        if ($region=='slav')       $id_region = $id_cher_slav;
        if ($region=='cher_nowoz') $id_region = $id_cher_nowoz;
        
        if ($region=='krp') $id_region = $id_krp;
        if ($region=='brz') $id_region = $id_brz;
        if ($region=='sosn') $id_region = $id_sosn;
        if ($region=='krk') $id_region = $id_krk;
        if ($region=='mena') $id_region = $id_mena;
        
        if ($region=='ns_m')
        {
           $where.= " and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = rep_lgt_f2_tmp.id and rs.id_region = 1 ) ";
        }
        if ($region=='ns_s')
        {
           $where.= " and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = rep_lgt_f2_tmp.id and rs.id_region = 2 ) ";
        }

        
        if (($id_region == 'null')&& ($region!='ns_m')&& ($region!='ns_s'))
           $region = '';
    }
    else
    {
     $id_region = 'null';
    }
    
    
    $p_mmgg = sql_field_val('mmgg', 'mmgg');
    
    $mmgg=$_POST['mmgg']; 
    
    
    list($day, $month, $year) = split('[/.-]', $mmgg);
    
    $p_id_usr = $session_user;
    
    
//------------------------------------------------------
    if ($oper == "dbf") {

    // if ($region=='slav')
       $result = pg_query($Link, "select seb_lgt_f2_slav_fun($p_mmgg,$id_region);");         
    // else    
    //   $result = pg_query($Link, "select seb_lgt_f2_fun($p_mmgg,$id_region);");
     
     
     if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        return;
     }
        
     
     $SQL = "select * from  syi_resinfo_tbl ;";
     
     if ($region=='slav')
         $SQL = "select * from  syi_resinfo_tbl where id_department = 330;";
     
     if (($region=='cher_res')||($region=='cher_nowoz'))
         $SQL = "select * from  syi_resinfo_tbl where id_department = 320;";
     
     $result = pg_query($Link, $SQL);
     if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        return;
     }
      $row = pg_fetch_array($result);
      $res_okpo =  $row['okpo_num'];

     
     $template_name = '../dbf/f2_empty.dbf';
     $dest_name = "../tmp/f2{$region}{$month}{$year}.dbf";
     
      if (!copy($template_name, $dest_name)) { 
           //echo "не удалось скопировать $dest_name";
           echo_result(2,"не удалось скопировать $dest_name");
           return;
      }
      chmod($dest_name, 0777);
      
      $db = dbase_open($dest_name, 2);
      
      if (!($db)) {
          //echo "ошибка открытия файла $dest_name";
          echo_result(2,"ошибка открытия файла $dest_name");
          return;
      }
      
      if ($region=='slav') 
      {
           //$bookcode = "substr(book,2,10)::varchar||'/'||code" ;
          $bookcode = "book||'/'||code" ;
          $sum_tarif = '0.0000 as sum_tarif';
          
          $res_okpo = '22815333';
      }
      else {
          $bookcode = "book||'/'||code" ;
          $sum_tarif = 'sum_tarif';
      }
          
      //28.08.2017 Бахмач - сказали 508 только для незонных
      
      $QE = "select * from (select ident_cod_l, fio_lgt, n_doc, $bookcode as bookcode, year, month, 
       case WHEN id_zone = 0       and ident in ('40','41','42','43','48','52','53','73','74','77','78') then 508 
            WHEN id_zone = 0       and ident not in ('40','41','42','43','48','52','53','73','74','77','78') then 504 
            WHEN id_zone in (8,10)  then 514 
            WHEN id_zone in (7,9)   then 524 
            WHEN id_zone =6         then 534 END as zone, 
       to_char(date_beg, 'YYYYMMDD')::varchar as date_beg, to_char(date_fin, 'YYYYMMDD')::varchar as date_fin,
       family_cnt,ident,percent, summ_lgt, demand_lgt, $sum_tarif, flag                               
       from rep_lgt_f2_tmp  where ident::int<1000 $where ) as ss order by  bookcode ,date_beg, zone ;"; 
/*
            WHEN id_zone in (8,10) and ident not in ('40','41','42','43','48','52','53','73','74','77','78') then 514 
            WHEN id_zone in (7,9)  and ident not in ('40','41','42','43','48','52','53','73','74','77','78') then 524 
            WHEN id_zone =6        and ident not in ('40','41','42','43','48','52','53','73','74','77','78') then 534 END as zone, 
*/      
      $res_e = pg_query($Link, $QE);
      
      if ($res_e) 
      {
          
         while ($row = pg_fetch_array($res_e)) { 
            
               $ident_cod_l =  iconv("utf-8","cp866", trim($row['ident_cod_l']));
               $fio_lgt =  iconv("utf-8","cp866",
                        str_replace("і","i",
                        str_replace("І","I",trim($row['fio_lgt']))));
               $n_doc =  iconv("utf-8","cp866", trim($row['n_doc']));
               $bookcode =  iconv("utf-8","cp866", trim($row['bookcode']));
             
               dbase_add_record($db, array($res_okpo, 
                   $ident_cod_l,
                   $fio_lgt,
                   $n_doc,
                   $bookcode,
                   $row['year'],
                   $row['month'],
                   $row['zone'],
                   $row['date_beg'],
                   $row['date_fin'],
                   $row['family_cnt'],
                   $row['ident'],
                   $row['percent'],
                   $row['summ_lgt'],
                   $row['demand_lgt'],
                   $row['sum_tarif'],
                   $row['flag']
                   ));   
         }
      }   
      else echo_result(2, pg_last_error($Link) );      
      
     dbase_close($db); 
          
     echo_result(-1,$dest_name);

     return;

    }
//------------------------------------------------------
  
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


