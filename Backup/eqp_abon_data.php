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

if ($sidx=='code')  $sidx="book ASC ,int_code $sord ,code";
if ($sidx=='book')  $sidx="book $sord,int_code ASC,code";

// connect to the database
//$fildsArray =DbGetFieldsArray($Link,'eqm_equipment_tbl');
$fildsArray = array();
$fildsArray['id'] = array('f_name' => 'id', 'f_type' => 'integer');
$fildsArray['code_eqp'] = array('f_name' => 'code_eqp', 'f_type' => 'integer');
$fildsArray['id_paccnt'] = array('f_name' => 'id_paccnt', 'f_type' => 'integer');

$fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
$fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
$fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
$fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');


$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_eqp = $_POST['eqp_id'];
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' code_eqp = '.$pid_eqp;


$Query="SELECT COUNT(*) AS count FROM (
  select m.id, m.code_eqp, m.id_paccnt, acc.book, acc.code, acc.note, 
  (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
  (adr.town||', '||adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
)::varchar as addr

from clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join clm_meterpoint_tbl as m on (acc.id = m.id_paccnt) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class) 
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
  select m.id, m.code_eqp, m.id_paccnt, acc.book, acc.code, acc.note, 
  (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
  (adr.town||', '||adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
)::varchar as addr,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code
from clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join clm_meterpoint_tbl as m on (acc.id = m.id_paccnt) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class) 
) as ss
  $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";

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