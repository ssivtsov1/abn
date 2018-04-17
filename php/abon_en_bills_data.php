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

if ($sidx=='code')  $sidx="int_book ASC, book ASC ,int_code $sord ,code";
if ($sidx=='book')  $sidx="int_book $sord, book $sord,int_code ASC,code";
if ($sidx=='demand')  $sidx=" coalesce(demand,-100000) $sord,book $sord,int_code ASC,code";
if ($sidx=='value')  $sidx=" coalesce(value,-100000) $sord,book $sord,int_code ASC,code";
if ($sidx=='value_calc')  $sidx=" coalesce(value_calc,-100000) $sord,book $sord,int_code ASC,code";
if ($sidx=='value_lgt')  $sidx=" coalesce(value_lgt,-100000) $sord,book $sord,int_code ASC,code";
if ($sidx=='value_subs')  $sidx=" coalesce(value_subs,-100000) $sord,book $sord,int_code ASC,code";


// connect to the database
$fildsArray =DbGetFieldsArray($Link,'acm_bill_tbl');
$fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
$fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
$fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
$fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
$fildsArray['sector'] = array('f_name' => 'sector', 'f_type' => 'character varying');
$fildsArray['user_name'] =   array('f_name'=>'user_name','f_type'=>'character varying');
$fildsArray['town'] = array('f_name' => 'town', 'f_type' => 'character varying');
$fildsArray['street'] = array('f_name' => 'street', 'f_type' => 'character varying');
$fildsArray['house'] = array('f_name' => 'house', 'f_type' => 'char');
$fildsArray['korp'] = array('f_name' => 'korp', 'f_type' => 'character varying');
$fildsArray['flat'] = array('f_name' => 'flat', 'f_type' => 'char');
$fildsArray['rem_worker'] = array('f_name' => 'rem_worker', 'f_type' => 'int');


$qWhere= DbBuildWhere($_POST,$fildsArray);

$p_mmgg = sql_field_val('p_mmgg', 'date');
$p_id_pref = sql_field_val('p_id_pref', 'integer');
$p_id_sector = sql_field_val('p_id_sector', 'integer');

if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere." mmgg = date_trunc('month', {$p_mmgg}::date) and id_pref = $p_id_pref ";
 
if ($p_id_sector!=0)
   $qWhere=$qWhere." and id_sector = $p_id_sector ";    

$Query="SELECT COUNT(*) AS count FROM ( 
 select b.*,  
 acc.book, acc.code, CASE WHEN acc.rem_worker THEN 1 END as rem_worker,
 (adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
 adr.town, adr.street, 
 (coalesce((acc.addr).house,'')||coalesce('/'||(acc.addr).slash,''))::varchar as house,
 (acc.addr).korp, 
 (coalesce((acc.addr).flat,'')|| coalesce('/'||(acc.addr).f_slash,''))::varchar as flat,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
rp.id_sector, coalesce(rs.name,'')::varchar as sector , u1.name as user_name 
from acm_bill_tbl as b
join clm_paccnt_tbl as acc on (acc.id = b.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
left join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
left join prs_runner_sectors as rs on (rs.id = rp.id_sector)
left join syi_user as u1 on (u1.id = b.id_person)
) as sss $qWhere;";

//throw new Exception(json_encode($Query));

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
 select b.*, 
 acc.book, acc.code, CASE WHEN acc.rem_worker THEN 1 END as rem_worker,
 (adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
 adr.town, adr.street, 
 (coalesce((acc.addr).house,'')||coalesce('/'||(acc.addr).slash,''))::varchar as house,
 (acc.addr).korp, 
 (coalesce((acc.addr).flat,'')|| coalesce('/'||(acc.addr).f_slash,''))::varchar as flat,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
rp.id_sector, coalesce(rs.name,'')::varchar as sector, u1.name as user_name 
from acm_bill_tbl as b
join clm_paccnt_tbl as acc on (acc.id = b.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
left join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
left join prs_runner_sectors as rs on (rs.id = rp.id_sector)
left join syi_user as u1 on (u1.id = b.id_person)
) as ss
  $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";

$f=fopen('aaa.sql','w+');
fputs($f,$SQL);

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