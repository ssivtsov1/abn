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

      
    $oper = $_POST['oper'];
/*
    if (isset($_POST['submitButton'])) 
    {    
        $region=$_POST['submitButton'];
         
        if ($region=='desn') $id_region = $id_desn;
        if ($region=='nowoz') $id_region = $id_novoz;
        if ($region=='cher') $id_region = $id_cherr;
    }
*/    
    
    $p_mmgg = sql_field_val('mmgg', 'mmgg');
    
    $mmgg=$_POST['mmgg']; 
    
    
    list($day, $month, $year) = split('[/.-]', $mmgg);
    
    $p_id_usr = $session_user;
    
    
//------------------------------------------------------
    if ($oper == "dbf") {

/*      
     $result = pg_query($Link, "select seb_lgt_f2_fun($p_mmgg,$id_region);");
     if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        return;
     }
*/        
     $template_name = '../dbf/oschad_empty.dbf';
     $dest_name = "../tmp/oschad{$region}{$month}{$year}.dbf";
     
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
      
     $result = pg_query($Link, "select * from  syi_resinfo_tbl ;");
     if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        return;
     }
      $row = pg_fetch_array($result);
      $res_okpo =  $row['id_department'];
      
     $result = pg_query($Link, "select value_ident::int as value from syi_sysvars_tbl where ident='id_res'; ");
     if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        return;
     }
      $row = pg_fetch_array($result);
      $res_code =  $row['value'];
      
      
      $QE = "SELECT $res_code as id_dep, 
      to_char(( $p_mmgg::date +'1 month'::interval)::date, 'YYYYMMDD')::varchar as period,
      c.index_accnt , c.book,c.code,(a.last_name||' '||a.name|| ' '||a.patron_name)::varchar as abon,
 (CASE WHEN $res_code <> 310 THEN adr.town||' ' ELSE '' END ||(adr.street||' '||
        (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')|| 
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash,''))::varchar
    ))::varchar as addr,
substring(adr.ident,1,10)::varchar as town, adr.ident,
fun_parse_leter((c.addr).house,1) as house,
fun_parse_leter((c.addr).house,2) as house_2,
fun_parse_leter((c.addr).korp,1) as korp,
fun_parse_leter((c.addr).flat,1) as flat,
fun_parse_leter((c.addr).f_slash,1) as f_slash,
round(CASE when s.e_val <0 then 0.00 else s.e_val end,2) as e_val,
('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
('0'||substring(c.book FROM '[0-9]+'))::int as int_book       
 from  clm_paccnt_tbl c
 join acm_saldo_tbl  s  on (s.id_paccnt=c.id and s.mmgg= $p_mmgg and s.id_pref = 10 )
 join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
 join clm_abon_tbl a on (a.id=c.id_abon )
where   
  c.archive=0 
 order by int_book, c.book, int_code, c.code ;"; 

 //echo $QE;
 
      $res_e = pg_query($Link, $QE);
      
      if ($res_e) 
      {
          
         while ($row = pg_fetch_array($res_e)) { 
               
               $fio =  iconv("utf-8","cp1251",
                        str_replace("і","i",
                        str_replace("І","I",trim($row['abon']))));
               $fio = '';

               $addr =  iconv("utf-8","cp1251",
                        str_replace("і","i",
                        str_replace("І","I",trim($row['addr']))));

               $house_letter =  iconv("utf-8","cp1251",
                        str_replace("і","i",
                        str_replace("І","I",trim($row['house_2']))));

               $book =  iconv("utf-8","cp1251",
                        str_replace("і","i",
                        str_replace("І","I",trim($row['book']))));

               $code =  iconv("utf-8","cp1251",
                        str_replace("і","i",
                        str_replace("І","I",trim($row['code']))));
               
               dbase_add_record($db, array(
                   $row['id_dep'],
                   $row['period'],
                   $row['index_accnt'],
                   $book,
                   $code,
                   $fio,
                   $addr,
                   $row['town'],
                   $row['ident'],
                   $row['house'],
                   $house_letter,
                   $row['korp'],
                   $row['flat'],
                   $row['f_slash'],
                   $row['e_val']
                   ));   
         }
      }         
      
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


