<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(1);

$Link = get_database_link($_SESSION,1);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();
  
$p_book=sql_field_val('book','string');    
$p_code=sql_field_val('code','string');    
//$p_id=sql_field_val('id_paccnt','int');  


$SQL = "select acc.id from clm_paccnt_tbl as acc where acc.book = $p_book and acc.code = $p_code ;";
$result = pg_query($Link,$SQL) ;
if ($result) {
    
     $rows = pg_num_rows($result);
     if ($rows>0)
     {     
      $row = pg_fetch_array($result);
      $p_id = $row['id'];
     }
     else
     {
         echo_result(2,'Невідомий споживач!!');
         return;
     }     
};

$SQL = "select acc.id, acc.book, acc.code, coalesce(acm.book||'/'||acm.code,'')::varchar as main_abon, 
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 (adr.adr||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))||coalesce(', &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  дільниця: '||rs.name,'')::varchar
)::varchar as addr, coalesce(round(sal.e_val,2),0) as  e_val, coalesce(acc.archive,0) as archive, coalesce(round(bill_sum,2),0) as bill_sum
from clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
left join clm_paccnt_tbl as acm on (acm.id = acc.main_id) 
left join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
left join prs_runner_sectors as rs on (rp.id_sector = rs.id) 
left join acm_saldo_tbl as sal on (sal.id_paccnt = $p_id and sal.id_pref = 10 and sal.mmgg =  fun_mmgg())
left join (
    select bm.id_paccnt, GREATEST(0,coalesce(sum(b1.value),0)- coalesce(sum(su.subs),0)+ CASE WHEN sum(su.subs) is null THEN coalesce(sum(ba.value),0) ELSE 0 END) as bill_sum
    from
   (select id_paccnt, max(mmgg) as mmgg from acm_bill_tbl where id_pref = 10 and id_paccnt = $p_id and idk_doc = 200 group by id_paccnt) as bm
   join acm_bill_tbl as b1 on (b1.id_paccnt = $p_id and b1.id_pref = 10 and b1.idk_doc = 200 and b1.mmgg =bm.mmgg  and b1.mmgg_bill = bm.mmgg)
   join acm_bill_tbl as ba on (ba.id_paccnt = $p_id and ba.id_pref = 12 and ba.mmgg =bm.mmgg  )
   left join (select p.mmgg, sum(value) as subs
     from acm_pay_tbl as p 
     where p.id_paccnt = $p_id 
     and p.id_pref = 10 and p.idk_doc in ( 110,111, 193, 194 )
     group by p.mmgg
   ) as su on (su.mmgg = bm.mmgg )
   group by bm.id_paccnt
) as bb on (bb.id_paccnt = acc.id)
where (acc.id = $p_id );";

//where (acc.book = $p_book and acc.code = $p_code ) or (acc.id = $p_id );";

$result = pg_query($Link,$SQL) ;

 if ($result) {

     $rows = pg_num_rows($result);
     if ($rows>0)
     {     
       $row = pg_fetch_array($result);
       if ($row['archive']==1 )
           
         if($row['main_abon']!='')
           $arr = array('errcode' => -1, 'id' => $row['id'], 'book' => $row['book'], 'code' => $row['code'], 
              'abon' => $row['abon'], 'addr' => $row['addr'].' АРХІВ!'.'(дійсний рах. '.$row['main_abon'].')', 'saldo' => $row['e_val'] ,'bill_sum' => $row['bill_sum']);
         else    
           $arr = array('errcode' => -1, 'id' => $row['id'], 'book' => $row['book'], 'code' => $row['code'], 
              'abon' => $row['abon'], 'addr' => $row['addr'].' АРХІВ!!!', 'saldo' => $row['e_val'] ,'bill_sum' => $row['bill_sum']);
       else
         $arr = array('errcode' => 1, 'id' => $row['id'], 'book' => $row['book'], 'code' => $row['code'], 
              'abon' => $row['abon'], 'addr' => $row['addr'], 'saldo' => $row['e_val'],'bill_sum' => $row['bill_sum']);
       
       echo json_encode($arr);
     }
     else
     {
         echo_result(2,'Невідомий споживач!');
     }
   }
 else        {
     echo_result(2,pg_last_error($Link));
     }

?>
