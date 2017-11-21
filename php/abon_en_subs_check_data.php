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

if ($sidx=='code')  $sidx="int_book $sord , book $sord , int_code $sord, code ";
if ($sidx=='book')  $sidx="int_book $sord , book $sord , int_code $sord, code ";


 // get how many rows we want to have into the grid

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

// connect to the database
//$fildsArray =DbGetFieldsArray($Link,'acm_indication_tbl');
$fildsArray['id'] = array('f_name' => 'id', 'f_type' => 'integer');
$fildsArray['id_paccnt'] = array('f_name' => 'id_paccnt', 'f_type' => 'integer');
$fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
$fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
$fildsArray['num_subs'] = array('f_name' => 'num_subs', 'f_type' => 'character varying');
$fildsArray['bookcod'] = array('f_name' => 'bookcod', 'f_type' => 'character varying');
$fildsArray['calc_book'] = array('f_name' => 'calc_book', 'f_type' => 'character varying');
$fildsArray['calc_code'] = array('f_name' => 'calc_code', 'f_type' => 'character varying');
$fildsArray['pbookcod'] = array('f_name' => 'pbookcod', 'f_type' => 'char');
$fildsArray['acc_subs_num'] = array('f_name' => 'acc_subs_num', 'f_type' => 'character varying');
$fildsArray['fio'] = array('f_name' => 'fio', 'f_type' => 'character varying');
$fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
$fildsArray['town'] = array('f_name' => 'town', 'f_type' => 'character varying');
$fildsArray['addr_abon'] = array('f_name' => 'addr_abon', 'f_type' => 'character varying');
//$fildsArray['last_name'] = array('f_name' => 'last_name', 'f_type' => 'character varying');
//$fildsArray['name'] = array('f_name' => 'name', 'f_type' => 'character varying');
//$fildsArray['patron_name'] = array('f_name' => 'patron_name', 'f_type' => 'character varying');

$fildsArray['abon_name'] = array('f_name' => 'abon_name', 'f_type' => 'character varying');
$fildsArray['abon_s'] = array('f_name' => 'abon_s', 'f_type' => 'character varying');
$fildsArray['fcurrent'] = array('f_name' => 'fcurrent', 'f_type' => 'integer'); 
$fildsArray['ident'] = array('f_name' => 'ident', 'f_type' => 'integer');


$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$mmgg = $row['mmgg'];

$qWhere= DbBuildWhere($_POST,$fildsArray);
/*
$pmmgg = sql_field_val('p_mmgg', 'date'); 
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' mmgg = '.$pmmgg;
*/

if (isset($_POST['region'])&& (trim($_POST['region'])!='')&& (trim($_POST['region'])!='0')&&
        (trim($_POST['region'])!='null'))
{
    $p_id_region = $_POST['region'];
    
    if ($qWhere!='') $qWhere=$qWhere.' and ';
    else $qWhere=' where ';

    if ($p_id_region!=999)
    {
      $qWhere.= " exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = sss.id_paccnt and rs.id_region =  $p_id_region ) ";
    }
    else
    {
        $qWhere.= " sss.id_paccnt is null";
    }
    
}

$Query="SELECT COUNT(*) AS count FROM (
    select  a.book,a.code,a.num_subs,a.bookcod,a.calc_book,a.calc_code,a.num_subsaccnt as acc_subs_num,a.fio ,
    p.book||'/'||p.code as pbookcod,  
    coalesce(p.last_name,'')||' '||coalesce(p.name,'')||' '||coalesce(p.patron_name,'') as abon_name, 
    ps.book||'/'||ps.code||' '||coalesce(ps.last_name,'')||' '||coalesce(ps.name,'')||' '||coalesce(ps.patron_name,'') as abon_s, 
    a.ident, p.id as id_paccnt,
    (coalesce(a.name_town,'')||coalesce(' вул.'||a.name_street,'')||coalesce(' буд.'||a.house,'')||coalesce(' корп.'||a.corp,'')||coalesce(' кв.'||a.flat,'') )::varchar as addr,
    (adr.street||' '||
       (coalesce('буд.'||(p.addr).house||'','')||
		coalesce('/'||(p.addr).slash||' ','')||
			coalesce(' корп.'||(p.addr).korp||'','')||
				coalesce(', кв. '||(p.addr).flat,'')||
					coalesce('/'||(p.addr).f_slash||' ',''))::varchar
    )::varchar as addr_abon,adr.town,
    ('0'||substring(a.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(a.book FROM '[0-9]+'))::int as int_book,
    CASE WHEN s.id_subs is not null THEN 1 END as fcurrent
    from aci_subs_tbl as a
    left join (select c.id, c.book, c.code,c.addr, a.name,a.last_name,a.patron_name from clm_paccnt_tbl c,clm_abon_tbl a where c.id_abon=a.id order by c.id) as p
      on (a.id_paccnt = p.id )
    left join (select distinct id_subs from acm_subs_tbl where mmgg = '$mmgg' and subs_all <>0 and id_subs is not null order by id_subs) as s on (a.id = s.id_subs)
    left join adt_addr_town_street_tbl as adr on (adr.id = (p.addr).id_class) 
    left join (select c.id,c.book, c.code, a.name,a.last_name,a.patron_name from clm_paccnt_tbl c,clm_abon_tbl a where c.id_abon=a.id order by c.id) as ps
      on (a.subs_paccnt = ps.id )
) as sss $qWhere;";
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
   select  a.id,a.book,a.code,a.num_subs,a.bookcod,a.calc_book,a.calc_code,a.num_subsaccnt as acc_subs_num,a.fio ,
   p.book||'/'||p.code as pbookcod,  
   coalesce(p.last_name,'')||' '||coalesce(p.name,'')||' '||coalesce(p.patron_name,'') as abon_name, 
   ps.book||'/'||ps.code||' '||coalesce(ps.last_name,'')||' '||coalesce(ps.name,'')||' '||coalesce(ps.patron_name,'') as abon_s, 
   a.ident, p.id as id_paccnt,
   (coalesce(a.name_town,'')||coalesce(' вул.'||a.name_street,'')||coalesce(' буд.'||a.house,'')||coalesce(' корп.'||a.corp,'')||coalesce(' кв.'||a.flat,'') )::varchar as addr,
    (adr.street||' '||
       (coalesce('буд.'||(p.addr).house||'','')||
		coalesce('/'||(p.addr).slash||' ','')||
			coalesce(' корп.'||(p.addr).korp||'','')||
				coalesce(', кв. '||(p.addr).flat,'')||
					coalesce('/'||(p.addr).f_slash||' ',''))::varchar
    )::varchar as addr_abon, adr.town,
    ('0'||substring(a.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(a.book FROM '[0-9]+'))::int as int_book,
    CASE WHEN s.id_subs is not null THEN 1 END as fcurrent
    from aci_subs_tbl as a
    left join (select distinct id_subs from acm_subs_tbl where mmgg = '$mmgg' and subs_all <>0 and id_subs is not null order by id_subs) as s on (a.id = s.id_subs)
    left join (select c.id,c.book, c.code, c.addr,a.name,a.last_name,a.patron_name from clm_paccnt_tbl c,clm_abon_tbl a where c.id_abon=a.id order by c.id) as p
      on (a.id_paccnt = p.id )
    left join adt_addr_town_street_tbl as adr on (adr.id = (p.addr).id_class) 
    left join (select c.id,c.book, c.code, a.name,a.last_name,a.patron_name from clm_paccnt_tbl c,clm_abon_tbl a where c.id_abon=a.id order by c.id) as ps
      on (a.subs_paccnt = ps.id )
) as sss
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