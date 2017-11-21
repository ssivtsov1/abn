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

// connect to the database
$fildsArray =DbGetFieldsArray($Link,'acm_dop_lgt_tbl');
$fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
$fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
$fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
$fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
$fildsArray['user_name'] = array('f_name' => 'user_name', 'f_type' => 'character varying');
$fildsArray['alt_code'] = array('f_name' => 'alt_code', 'f_type' => 'integer');
$fildsArray['subs_value'] = array('f_name' => 'subs_value', 'f_type' => 'numeric');


$qWhere= DbBuildWhere($_POST,$fildsArray);

$p_mmgg = sql_field_val('p_mmgg', 'mmgg');

if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere." mmgg = $p_mmgg::date ";
 

$Query="SELECT COUNT(*) AS count FROM (
 select lg.*, i.name as lgt_name, i.ident, i.alt_code,u1.name as user_name,
 acc.book, acc.code, 
 (adr.adr||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr, pp.subs_value,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon
from acm_dop_lgt_tbl as lg
join clm_paccnt_tbl as acc on (acc.id = lg.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
left join lgi_group_tbl as i on (i.id = lg.id_grp_lgt)
left join syi_user as u1 on (u1.id = lg.id_person)
left join (
   	select p.id_paccnt, sum(p.value) as subs_value
       	from acm_pay_tbl as p 
       	where p.mmgg = $p_mmgg::date and p.id_pref = 10 and p.idk_doc in (110, 111, 193,194) 
       	group by p.id_paccnt
        having sum(p.value) >0
	order by id_paccnt
       ) as pp on (pp.id_paccnt = acc.id)
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

$SQL = "select * from ( select lg.*, i.name as lgt_name, i.ident, i.alt_code,u1.name as user_name,
 acc.book, acc.code, 
 (adr.adr||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon, pp.subs_value,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book
from acm_dop_lgt_tbl as lg
join clm_paccnt_tbl as acc on (acc.id = lg.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
left join lgi_group_tbl as i on (i.id = lg.id_grp_lgt)
left join syi_user as u1 on (u1.id = lg.id_person)
left join 
 (
   select p.id_paccnt, sum(p.value) as subs_value
   from acm_pay_tbl as p 
   where p.mmgg = $p_mmgg::date and p.id_pref = 10 and p.idk_doc in (110, 111, 193,194) 
   group by p.id_paccnt
   having sum(p.value) >0
   order by id_paccnt
  ) as pp on (pp.id_paccnt = acc.id)
) as ss
  $qWhere Order by $sidx $sord ,id  LIMIT $limit OFFSET $start ";


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