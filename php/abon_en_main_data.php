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

if (!$sidx)
    $sidx = 2;
if (!$limit)
    $limit = 500; 
if (!$page)
    $page = 1;

if ($sidx=='code')  $sidx="book ASC ,int_code $sord ,code";
if ($sidx=='book')  $sidx="book $sord,int_code ASC,code";

// connect to the database
//$fildsArray =DbGetFieldsArray($Link,'clm_paccnt_tbl');
$fildsArray = array();
$fildsArray['id'] = array('f_name' => 'id', 'f_type' => 'integer');
$fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
$fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
$fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
$fildsArray['town'] = array('f_name' => 'town', 'f_type' => 'character varying');
$fildsArray['street'] = array('f_name' => 'street', 'f_type' => 'character varying');
$fildsArray['house'] = array('f_name' => 'house', 'f_type' => 'char');
$fildsArray['korp'] = array('f_name' => 'korp', 'f_type' => 'character varying');
$fildsArray['flat'] = array('f_name' => 'flat', 'f_type' => 'char');

$fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
$fildsArray['note'] = array('f_name' => 'note', 'f_type' => 'character varying');
$fildsArray['lgt']  = array('f_name' => 'lgt', 'f_type' => 'character varying');
$fildsArray['action'] = array('f_name' => 'action', 'f_type' => 'integer');
$fildsArray['archive'] = array('f_name' => 'archive', 'f_type' => 'integer');

$qWhere = DbBuildWhere($_POST, $fildsArray);

$parch = $_POST['arch_mode'];
if ($parch==0)
{
  if ($qWhere!='') $qWhere=$qWhere.' and ';
  else $qWhere=' where ';

  $qWhere=$qWhere.' archive =0 ';
}

if ($parch==1)
{ 
  if ($qWhere!='') $qWhere=$qWhere.' and ';
  else $qWhere=' where ';

  $qWhere=$qWhere.' archive =1 ';
}


$Query = "SELECT COUNT(*) AS count FROM 
(select acc.id, acc.book, acc.code, acc.note, acc.archive, sw.action,
 adr.town, (adr.street||coalesce(' ('||adi.name_old||')',''))::varchar as street, 
 (coalesce((acc.addr).house,'')||coalesce('/'||(acc.addr).slash,''))::varchar as house,
 (acc.addr).korp, 
 (coalesce((acc.addr).flat,'')|| coalesce('/'||(acc.addr).f_slash,''))::varchar as flat,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 CASE WHEN id_lgt is not null THEN 'П' END as lgt,
(adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
)::varchar as addr
from clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
left join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class) 
left join adi_class_tbl as adi on (adi.id = (acc.addr).id_class)
left join 
(
       select csw.id_paccnt, csw.dt_action, csw.action
       from clm_switching_tbl as csw 
        join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl  group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and csw.dt_action = cswc.dt_action and cswc.action in (3,4)) 
       where csw.action not in (3,4) and cswc.id_paccnt is null 

) as sw on (sw.id_paccnt = acc.id)
left join (
  select id_paccnt, max(la.id) as id_lgt
  from lgm_abon_tbl  as la
  where (dt_end is null) or (dt_end > now()::date) group by id_paccnt order by id_paccnt
 )  as lg on (lg.id_paccnt = acc.id)

) as ss  $qWhere;";

/*
 (adr.adr||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
)::varchar as addr,
*/

$result = pg_query($Link, $Query) or die("SQL Error: " . pg_last_error($Link));
$row = pg_fetch_array($result);
$count = $row['count'];

//print("<br> count =  $count "); 

if ($count > 0 && $limit > 0) {
    $total_pages = ceil($count / $limit);
} else {
    $total_pages = 0;
}

if ($page > $total_pages)
    $page = $total_pages;

$start = $limit * $page - $limit; // do not put $limit*($page - 1)
if ($start < 0)
    $start = 0;
//address_print(acc.addr)
//('0'||regexp_replace(regexp_replace(acc.code, '-.*?$', '') , '[^0-9]', '','g'))::int as int_code,

$SQL = "select * from (select acc.id, acc.book, acc.code, acc.note, acc.archive,sw.action,
 ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
  adr.town, (adr.street||coalesce('('||adi.name_old||')',''))::varchar as street, 
 (coalesce((acc.addr).house,'')||coalesce('/'||(acc.addr).slash,''))::varchar as house,
 (acc.addr).korp, 
 (coalesce((acc.addr).flat,'')|| coalesce('/'||(acc.addr).f_slash,''))::varchar as flat,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
CASE WHEN id_lgt is not null THEN 'П' END as lgt,
(adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
)::varchar as addr
from clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
left join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
left join adi_class_tbl as adi on (adi.id = (acc.addr).id_class)
left join 
(
       select csw.id_paccnt, csw.dt_action, csw.action
       from clm_switching_tbl as csw 
        join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl  group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and csw.dt_action = cswc.dt_action and cswc.action in (3,4)) 
       where csw.action not in (3,4) and cswc.id_paccnt is null 
) as sw on (sw.id_paccnt = acc.id)
left join (
  select id_paccnt, max(la.id) as id_lgt
  from lgm_abon_tbl  as la
  where (dt_end is null) or (dt_end > now()::date) group by id_paccnt order by id_paccnt
 )  as lg on (lg.id_paccnt = acc.id)
) as ss
  $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";

//throw new Exception(json_encode($SQL));

$result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
if (!$result) {
    print("<br> no records found");
} else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

    $i = 0;
    while ($row = pg_fetch_array($result)) {

        $data['rows'][$i]['cell'][] = $row['book'];
        $data['rows'][$i]['cell'][] = $row['code'];
        $data['rows'][$i]['cell'][] = $row['addr'];
        $data['rows'][$i]['cell'][] = $row['town'];
        $data['rows'][$i]['cell'][] = $row['street'];
        $data['rows'][$i]['cell'][] = $row['house'];
        $data['rows'][$i]['cell'][] = $row['korp'];
        $data['rows'][$i]['cell'][] = $row['flat'];
        
        $data['rows'][$i]['cell'][] = $row['abon'];
        $data['rows'][$i]['cell'][] = $row['note'];
        $data['rows'][$i]['cell'][] = $row['lgt'];
        $data['rows'][$i]['cell'][] = $row['action'];
        $data['rows'][$i]['cell'][] = $row['archive'];
        $data['rows'][$i]['cell'][] = $row['id'];        
        $i++;
    }

}   


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);
?>