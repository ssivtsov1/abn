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
$fildsArray['town'] = array('f_name' => 'town', 'f_type' => 'character varying');
$fildsArray['street'] = array('f_name' => 'street', 'f_type' => 'character varying');
$fildsArray['house'] = array('f_name' => 'house', 'f_type' => 'char');
$fildsArray['korp'] = array('f_name' => 'korp', 'f_type' => 'character varying');
$fildsArray['flat'] = array('f_name' => 'flat', 'f_type' => 'char');
$fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
$fildsArray['note'] = array('f_name' => 'note', 'f_type' => 'character varying');

$qWhere = DbBuildWhere($_POST, $fildsArray); 


$Query = "SELECT COUNT(*) AS count FROM 
(select acc.id, acc.book, acc.code, acc.note, acc.archive, 
adr.town,  
 (adr.street||coalesce(' ('||adi.name_old||')',''))::varchar as street, 
 (coalesce((acc.addr).house,'')||coalesce('/'||(acc.addr).slash,''))::varchar as house,
 (acc.addr).korp, 
 (coalesce((acc.addr).flat,'')|| coalesce('/'||(acc.addr).f_slash,''))::varchar as flat,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 (adr.street||coalesce(' буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar as addr
from clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
join adi_class_tbl as adi on (adi.id = (acc.addr).id_class)
where archive =0
) as ss  $qWhere;";

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

$SQL = "select * from (select acc.id, acc.book, acc.code, acc.note, acc.archive,
 ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
 adr.town, (adr.street||coalesce(' ('||adi.name_old||')',''))::varchar as street, 
 (coalesce((acc.addr).house,'')||coalesce('/'||(acc.addr).slash,''))::varchar as house,
 (acc.addr).korp, 
 (coalesce((acc.addr).flat,'')|| coalesce('/'||(acc.addr).f_slash,''))::varchar as flat,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 (adr.street||coalesce(' буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar as addr
from clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
join adi_class_tbl as adi on (adi.id = (acc.addr).id_class)
where archive =0
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
        $data['rows'][$i]['cell'][] = $row['id'];        
        $data['rows'][$i]['cell'][] = $row['book'];
        $data['rows'][$i]['cell'][] = $row['code'];
        $data['rows'][$i]['cell'][] = $row['town'];
        $data['rows'][$i]['cell'][] = $row['street'];
        $data['rows'][$i]['cell'][] = $row['house'];
        $data['rows'][$i]['cell'][] = $row['korp'];
        $data['rows'][$i]['cell'][] = $row['flat'];
        $data['rows'][$i]['cell'][] = $row['abon'];
        $data['rows'][$i]['cell'][] = $row['note'];
        $data['rows'][$i]['cell'][] = $row['addr'];
        $i++;
    }

}   


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);
?>