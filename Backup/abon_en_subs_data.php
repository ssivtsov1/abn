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

if ($sidx=='code')  $sidx="int_code $sord ,book ASC ,code";
if ($sidx=='book')  $sidx="book $sord,int_code ASC,code";

// connect to the database
$fildsArray =DbGetFieldsArray($Link,'acm_subs_tbl');
$fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
$fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
$fildsArray['paccnt'] = array('f_name' => 'paccnt', 'f_type' => 'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$p_mmgg = sql_field_val('p_mmgg', 'date');

if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere." mmgg = date_trunc('month', {$p_mmgg}::date) ";



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
            where rp.id_paccnt = ss.id_paccnt and rs.id_region =  $p_id_region ) ";
    }
    else
    {
        $qWhere.= " ss.id_paccnt is null";
    }
    
}
 

$selected_id = 0;
if (isset($_POST['selected_id']))  
{
 $selected_id = $_POST['selected_id'];
} 
$row_id = 0; 

$SQL = "select * from (
 select s.*, 
 (cs.name_town||' '||cs.name_street||' '|| 
   (coalesce('буд.'||cs.house||'','')||
			coalesce(cs.corp||'','')||
				coalesce(', кв. '||cs.flat,'') ))::varchar as addr, cs.fio as abon,
('0'||substring(s.code FROM '[0-9]+'))::int as int_code,
acc.book||'/'||acc.code||' '||
(adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
)::varchar||';'||
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as paccnt

from acm_subs_tbl as s
left join clm_paccnt_tbl as acc on (acc.id = s.id_paccnt)
left join clm_abon_tbl as c on (c.id = acc.id_abon) 
left join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
left join aci_subs_tbl as cs on (cs.id = s.id_subs)

) as ss
  $qWhere Order by $sidx $sord ";


//---------------- -- -- --------------- -- -- -----------------------------//

$Query = "CREATE temp SEQUENCE row_numbers_seq minvalue 1;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );

$Query = "CREATE temp TABLE dataset_tmp AS
         select nextval('row_numbers_seq') as cnt, * from ($SQL) as ss;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );


$Query="SELECT COUNT(*) AS count , 
 coalesce(sum(subs_month),0) as sum_subs_month, coalesce(sum(coalesce(ob_pay,0)+coalesce(ob_zon,0)),0) as sum_ob_pay,
 coalesce(sum(CASE WHEN coalesce(val_month,0) <> 0 THEN subs_all END ),0) as  sum_subs_all,
 coalesce(sum(CASE WHEN coalesce(val_month,0) = 0 THEN subs_all END ),0) as  sum_recalc    
FROM dataset_tmp ;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$count = $row['count'];

$sum_subs_month = $row['sum_subs_month'];
$sum_ob_pay = $row['sum_ob_pay'];
$sum_subs_all = $row['sum_subs_all'];
$sum_recalc = $row['sum_recalc'];

if( $count >0  && $limit > 0) {
	$total_pages = ceil($count/$limit);

} else {
	$total_pages = 0;
}
if ($page > $total_pages) $page=$total_pages;

if ($selected_id!=0) 
{
    $Query="select cnt, id from dataset_tmp where id_paccnt = $selected_id limit 1;";
    $result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
    if ($result)
    {
       $row = pg_fetch_array($result);
       $row_cnt = $row['cnt'];
       $row_id = $row['id'];
       if ($row_cnt>0)
       {
         $page = ceil($row_cnt/$limit);
       }
     }
}
 
$start = $limit*$page - $limit; // do not put $limit*($page - 1)
if($start < 0) $start = 0; 


/*


$Query="SELECT COUNT(*) AS count , sum(subs_month) as sum_subs_month, sum(ob_pay) as sum_ob_pay,
sum(CASE WHEN val_month <> 0 THEN subs_all END ) as  sum_subs_all,
sum(CASE WHEN val_month = 0 THEN subs_all END ) as  sum_recalc
FROM (
 select s.*, 
 (c.name_town||' '||c.name_street||' '||
   (coalesce('буд.'||c.house||'','')||
			coalesce(c.corp||'','')||
				coalesce(', кв. '||c.flat,'') ))::varchar as addr, c.fio as abon
from acm_subs_tbl as s
join aci_subs_tbl as c on (c.id = s.id_subs)
) as sss $qWhere;";

//throw new Exception(json_encode($Query));

$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$count = $row['count'];

$sum_subs_month = $row['sum_subs_month'];
$sum_ob_pay = $row['sum_ob_pay'];
$sum_subs_all = $row['sum_subs_all'];
$sum_recalc = $row['sum_recalc'];

//print("<br> count =  $count "); 

if( $count >0  && $limit > 0) {
	$total_pages = ceil($count/$limit);

} else {
	$total_pages = 0;
}

if ($page > $total_pages) $page=$total_pages;

$start = $limit*$page - $limit; // do not put $limit*($page - 1)
if($start < 0) $start = 0; 
*/

$SQL = "select * from dataset_tmp order by cnt LIMIT $limit OFFSET $start ";

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link).$SQL );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $data['userdata']['sum_ob_pay'] = $sum_ob_pay;
  $data['userdata']['sum_subs_all'] = $sum_subs_all;
  $data['userdata']['sum_subs_month'] = $sum_subs_month;
  $data['userdata']['sum_recalc'] = $sum_recalc;
  $data['userdata']['select_id'] = $row_id;

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