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

if ($sidx=='book')  $sidx="int_book $sord ,int_code $sord ,code";
if ($sidx=='code')  $sidx="int_book ASC , int_code $sord ,code";

// connect to the database
$fildsArray =DbGetFieldsArray($Link,'clm_lgt_manual_tbl');
$fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
$fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
$fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
$fildsArray['town'] = array('f_name' => 'town', 'f_type' => 'character varying'); 
$fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
$fildsArray['user_name'] =   array('f_name'=>'user_name','f_type'=>'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$p_mmgg = sql_field_val('p_mmgg', 'date');

if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere.=" mmgg = $p_mmgg "; 


$Query="SELECT COUNT(*) AS count FROM 
(
 select n.*, acc.book, acc.code, 
  (adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr, adr.town, 
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
u1.name as user_name 
from clm_lgt_manual_tbl as n 
join clm_paccnt_tbl as acc on (acc.id = n.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class) 
left join syi_user as u1 on (u1.id = n.id_person)
) as ss
$qWhere;";

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
 select n.*, acc.book, acc.code,  
 (adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')|| 
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr, adr.town,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
u1.name as user_name 
from clm_lgt_manual_tbl as n
join clm_paccnt_tbl as acc on (acc.id = n.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class) 
left join syi_user as u1 on (u1.id = n.id_person)
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