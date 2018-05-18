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

if ($sidx=='address')  $sidx="street_full $sord, int_house $sord, house $sord, korp $sord, int_flat $sord, int_code $sord, code $sord, id_zone ";
if ($sidx=='code')  $sidx="book $sord, int_code $sord, code ";
if ($sidx=='book')  $sidx="book $sord, int_code $sord, code ";

//$fildsArray =DbGetFieldsArray($Link,$table_name);
$fildsArray['id'] =   array('f_name'=>'id','f_type'=>'int');
$fildsArray['id_pack'] =   array('f_name'=>'id_pack','f_type'=>'int');
$fildsArray['id_paccnt'] =   array('f_name'=>'id_paccnt','f_type'=>'int');
$fildsArray['id_meter'] =   array('f_name'=>'id_meter','f_type'=>'int');
$fildsArray['id_type_meter'] =   array('f_name'=>'id_type_meter','f_type'=>'int');
$fildsArray['id_p_indic'] =   array('f_name'=>'id_p_indic','f_type'=>'int');

$fildsArray['book'] =   array('f_name'=>'book','f_type'=>'char');
$fildsArray['code'] =   array('f_name'=>'code','f_type'=>'char');
$fildsArray['address']= array('f_name'=>'address','f_type'=>'character varying');
$fildsArray['abon'] =   array('f_name'=>'abon','f_type'=>'character varying');

$fildsArray['num_meter'] =   array('f_name'=>'num_meter','f_type'=>'character varying');
$fildsArray['type_meter'] =   array('f_name'=>'type_meter','f_type'=>'character varying');
$fildsArray['carry'] =   array('f_name'=>'carry','f_type'=>'integer');
$fildsArray['k_tr'] =   array('f_name'=>'k_tr','f_type'=>'integer');
$fildsArray['id_zone'] =   array('f_name'=>'id_zone','f_type'=>'integer');

$fildsArray['p_indic'] =   array('f_name'=>'p_indic','f_type'=>'integer');
$fildsArray['p_demand'] =   array('f_name'=>'p_demand','f_type'=>'integer');
$fildsArray['dt_p_indic'] =   array('f_name'=>'dt_p_indic','f_type'=>'data');

$fildsArray['indic'] =   array('f_name'=>'indic','f_type'=>'integer');
$fildsArray['dt_indic'] =   array('f_name'=>'dt_indic','f_type'=>'data');

$fildsArray['demand'] =   array('f_name'=>'demand','f_type'=>'integer');

$fildsArray['indic_real'] =   array('f_name'=>'indic_real','f_type'=>'integer');
$fildsArray['act_num'] =   array('f_name'=>'act_num','f_type'=>'character varying');

$fildsArray['status'] =   array('f_name'=>'status','f_type'=>'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_pack = $_POST['p_id'];
if ($qWhere!='') $qWhere=$qWhere.' and '; 
else $qWhere=' where ';
$qWhere=$qWhere.' id_pack = '.$pid_pack;

//$sidx="int_book $sord, book $sord, int_code $sord, code $sord, id_zone ";
$sidx="street_full $sord, int_house $sord, house $sord, korp $sord, int_flat $sord, int_code $sord, code $sord, id_zone ";

 
$SQL = " select * from (select pd.*, acc.book, acc.code, pd.indic_real::int as indic_real_int, 
 adr.street||' '||address_print(acc.addr) as address, adr.street, (acc.addr).korp as korp,
  adr_full.adr as street_full,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 im.name as type_meter,i.value as p_indic, i.dat_ind as dt_p_indic ,i.value_cons as p_demand,
 round(calc_demand_carry(pd.indic,i.value,pd.carry),0)::int as demand,
('0'||coalesce(regexp_replace(regexp_replace((acc.addr).house, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_house,
(acc.addr).house as house, 
('0'||coalesce(regexp_replace(regexp_replace((acc.addr).flat, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_flat,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,   
CASE WHEN mp.id is null THEN '#' WHEN coalesce(acc.activ,false) = false then 'В' WHEN acc.not_live = true THEN 'Н' WHEN acc.idk_house=3 THEN 'Д' END as status
from 
clm_work_pack_data_tbl as pd 
join clm_paccnt_tbl as acc on (acc.id = pd.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join eqi_meter_tbl as im on (im.id = pd.id_type_meter)
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
join adt_addr_tbl as adr_full on (adr_full.id = (acc.addr).id_class)
left join acm_indication_tbl as i on (i.id = pd.id_p_indic)
left join clm_meterpoint_tbl as mp on (mp.id = pd.id_meter)
) as ss
  $qWhere Order by $sidx $sord ";

//---------------- -- -- --------------- -- -- -----------------------------//

$Query = "CREATE temp SEQUENCE row_numbers_seq minvalue 1;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );

$Query = "CREATE temp TABLE dataset_tmp AS
         select nextval('row_numbers_seq') as cnt, * from ($SQL) as ss;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );


$Query="SELECT COUNT(*) AS count FROM dataset_tmp ;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$count = $row['count'];

if( $count >0  && $limit > 0) {
	$total_pages = ceil($count/$limit);

} else {
	$total_pages = 0;
}
if ($page > $total_pages) $page=$total_pages;

$start = $limit*$page - $limit; // do not put $limit*($page - 1)
if($start < 0) $start = 0; 


$Query = "select * from dataset_tmp order by cnt LIMIT $limit OFFSET $start ";


//throw new Exception(json_encode($SQL));

$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {
      $data['rows'][$i]['cell'][] = $row['id'];
      
      $data['rows'][$i]['cell'][] = $row['id_pack'];
      $data['rows'][$i]['cell'][] = $row['id_paccnt'];
      $data['rows'][$i]['cell'][] = $row['id_meter'];
      $data['rows'][$i]['cell'][] = $row['id_type_meter'];
      $data['rows'][$i]['cell'][] = $row['id_p_indic'];
      $data['rows'][$i]['cell'][] = $row['status'];
      $data['rows'][$i]['cell'][] = $row['book'];
      $data['rows'][$i]['cell'][] = $row['code'];
      $data['rows'][$i]['cell'][] = $row['address'];
      $data['rows'][$i]['cell'][] = $row['abon'];

      $data['rows'][$i]['cell'][] = $row['num_meter'];     
      $data['rows'][$i]['cell'][] = $row['type_meter'];     
      
      $data['rows'][$i]['cell'][] = $row['carry'];     
      $data['rows'][$i]['cell'][] = $row['id_zone'];     

      $data['rows'][$i]['cell'][] = $row['p_demand'];     
      $data['rows'][$i]['cell'][] = $row['p_indic'];     

      $data['rows'][$i]['cell'][] = $row['dt_p_indic'];     
      $data['rows'][$i]['cell'][] = $row['indic'];     
      $data['rows'][$i]['cell'][] = $row['dt_indic'];           
      $data['rows'][$i]['cell'][] = $row['demand']; 

      $data['rows'][$i]['cell'][] = $row['indic_real_int']; 
      $data['rows'][$i]['cell'][] = $row['act_num'];     
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>