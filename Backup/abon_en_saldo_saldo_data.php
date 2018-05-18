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

$page = $_POST['page']; // get the requested page
$limit = $_POST['rows']; // get how many rows we want to have into the grid
$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
$sord = $_POST['sord']; // get the direction

 // get how many rows we want to have into the grid

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

// connect to the database
$fildsArray =DbGetFieldsArray($Link,'acm_saldo_tbl');
$fildsArray['subs_all']  = array('f_name' => 'subs_all', 'f_type' => 'numeric');
$fildsArray['sw_action']  = array('f_name' => 'sw_action', 'f_type' => 'varchar');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_paccnt = $_POST['p_id'];
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere." id_paccnt = $pid_paccnt and mmgg <=fun_mmgg() ";
//$qWhere2=' and p.id_paccnt = '.$pid_paccnt;


$Query="SELECT COUNT(*) AS count FROM (
 select n.*, sb.subs_all, CASE WHEN sw.action = 1 THEN 'В' WHEN sw.action = 2 THEN 'П' END::varchar as sw_action
 from acm_saldo_tbl as n
left join (select id_paccnt, mmgg, sum(value) as subs_all 
    from acm_pay_tbl as p where p.id_pref = 10 and p.idk_doc in (110, 111, 193,194)  
      and p.id_paccnt = $pid_paccnt
      group by id_paccnt, mmgg) as sb 
 on (sb.id_paccnt = n.id_paccnt and sb.mmgg = n.mmgg and n.id_pref = 10)
left join 
 (
       select csw.dt_action, csw.action, csw.mmgg
       from clm_switching_tbl as csw 
        join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.id_paccnt = $pid_paccnt and  csw.action not in (3,4) and cswc.id_paccnt is null 
 ) as sw on (sw.mmgg <= n.mmgg and n.id_pref = 10)

) as ss $qWhere;";

$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$count = $row['count'];

//print("<br> count =  $count "); 

if( $count >0  && $limit > 0) {
	$total_pages = ceil($count/$limit);

} else {
	$total_pages = 0;
}

if ($page > $total_pages) $page=$total_pages;

$start = $limit*$page - $limit; // do not put $limit*($page - 1)
if($start < 0) $start = 0; 

$SQL = "select * from (
 select n.*, sb.subs_all, CASE WHEN sw.action = 1 THEN 'В' WHEN sw.action = 2 THEN 'П' END::varchar as sw_action
 from acm_saldo_tbl as n
 left join (select id_paccnt, mmgg, sum(value) as subs_all 
    from acm_pay_tbl as p where p.id_pref = 10 and p.idk_doc in (110, 111, 193,194)  
      and p.id_paccnt = $pid_paccnt
      group by id_paccnt, mmgg) as sb 
 on (sb.id_paccnt = n.id_paccnt and sb.mmgg = n.mmgg and n.id_pref = 10)
left join 
(
       select csw.dt_action, csw.action, csw.mmgg
       from clm_switching_tbl as csw 
        join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.id_paccnt = $pid_paccnt and  csw.action not in (3,4) and cswc.id_paccnt is null 
) as sw on ( sw.mmgg <= n.mmgg and n.id_pref = 10)

) as ss
  $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";

/*
  left join (select id_paccnt, mmgg, sum(subs_all) as subs_all 
    from acm_subs_tbl where $qWhere2 group by id_paccnt, mmgg) as sb 
     on (sb.id_paccnt = n.id_paccnt and sb.mmgg = n.mmgg and n.id_pref = 10)

 */


$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link).$SQL );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {

     foreach ($fildsArray as $fild) {
         $data['rows'][$i][$fild['f_name']] = $row[$fild['f_name']];
     }

    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>