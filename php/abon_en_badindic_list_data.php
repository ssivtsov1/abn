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

if ($sidx=='code')  $sidx="book $sord ,code";
if ($sidx=='book')  $sidx="book $sord ,code";


 // get how many rows we want to have into the grid

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

// connect to the database
$fildsArray =DbGetFieldsArray($Link,'acm_indication_tbl');
$fildsArray['type_meter'] = array('f_name' => 'type_meter', 'f_type' => 'character varying');
$fildsArray['is_manual'] = array('f_name' => 'is_manual', 'f_type' => 'integer');
$fildsArray['p_indic'] = array('f_name' => 'p_indic', 'f_type' => 'integer');
$fildsArray['num_pack'] = array('f_name' => 'num_pack', 'f_type' => 'character varying');
$fildsArray['id_hwork'] = array('f_name' => 'id_hwork', 'f_type' => 'integer');
$fildsArray['idk_work'] = array('f_name' => 'idk_work', 'f_type' => 'integer');

$fildsArray['book'] =   array('f_name'=>'book','f_type'=>'char');
$fildsArray['code'] =   array('f_name'=>'code','f_type'=>'char');
//$fildsArray['address']= array('f_name'=>'address','f_type'=>'character varying');
$fildsArray['abon'] =   array('f_name'=>'abon','f_type'=>'character varying');
$fildsArray['error_text'] =   array('f_name'=>'error_text','f_type'=>'character varying');


$qWhere= DbBuildWhere($_POST,$fildsArray);

$pmmgg = sql_field_val('p_mmgg', 'date'); 
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' mmgg = '.$pmmgg;


$Query="SELECT COUNT(*) AS count FROM (
  select c.code, c.book, 
   (a.last_name||' '||coalesce(a.name,'')||' '||coalesce(a.patron_name,''))::varchar as abon, sss.* 
  from 
  (select * from 
  (select n.*, m.name as type_meter,i2.value as p_indic,
   CASE WHEN n.id_ind is null and n.id_work is null THEN 1 ELSE 0 END as is_manual,
   ph.num_pack, 
   find_prev_indic_fun(n.id_paccnt,n.id_meter,n.id_zone,n.id_energy,n.dat_ind) as calc_prev,
   'Помилкові попередні показники'::varchar as error_text
   from acm_indication_tbl as n
   left join eqi_meter_tbl as m on (m.id = n.id_typemet)
   left join acm_indication_tbl as i2 on (i2.id = n.id_prev)
   left join ind_pack_data as pd on (n.id_ind = pd.id)
   left join ind_pack_header as ph on (pd.id_pack = ph.id_pack)
   where n.mmgg = $pmmgg
  ) as ss 
  where coalesce(id_prev,0) <> coalesce(calc_prev,0)
 union all 
  select n.*, m.name as type_meter,i2.value as p_indic,
   CASE WHEN n.id_ind is null and n.id_work is null THEN 1 ELSE 0 END as is_manual,
   ph.num_pack , null as calc_prev, 'Є показники після демонтування'::varchar as error_text 
   from acm_indication_tbl as n
   left join eqi_meter_tbl as m on (m.id = n.id_typemet)
   left join acm_indication_tbl as i2 on (i2.id = n.id_prev)
   left join ind_pack_data as pd on (n.id_ind = pd.id)
   left join ind_pack_header as ph on (pd.id_pack = ph.id_pack)
   join (select id_meter, max(dat_ind) as max_dat from acm_indication_tbl group by id_meter order by id_meter) as mm
    on (mm.id_meter = n.id_meter )
   where n.id_operation  = 5 and mm.max_dat > n.dat_ind and n.mmgg = $pmmgg
 union all 
   select n.*, m.name as type_meter,i2.value as p_indic,
   CASE WHEN n.id_ind is null and n.id_work is null THEN 1 ELSE 0 END as is_manual,
   ph.num_pack, 
   null as calc_prev, 'Попередні показники більше наступних'::varchar as error_text
   from acm_indication_tbl as n
   join eqi_meter_tbl as m on (m.id = n.id_typemet)
   join acm_indication_tbl as i2 on (i2.id = n.id_prev)
   left join ind_pack_data as pd on (n.id_ind = pd.id)
   left join ind_pack_header as ph on (pd.id_pack = ph.id_pack)
    where i2.value > n.value and (n.value_cons = 0 or n.value_cons>5000) 
    and n.mmgg = $pmmgg
 union all 
   select n.*, m.name as type_meter,i2.value as p_indic,
   CASE WHEN n.id_ind is null and n.id_work is null THEN 1 ELSE 0 END as is_manual,
   ph.num_pack, 
   null as calc_prev, 'Не вказано зону або лічильник'::varchar as error_text
   from acm_indication_tbl as n
   left join eqi_meter_tbl as m on (m.id = n.id_typemet)
   left join ind_pack_data as pd on (n.id_ind = pd.id)
   left join ind_pack_header as ph on (pd.id_pack = ph.id_pack)
   left join acm_indication_tbl as i2 on (i2.id = n.id_prev)
    where (n.id_zone is null or n.id_meter is null)
    and n.mmgg = $pmmgg
) as sss
  join clm_paccnt_tbl as c on (c.id = sss.id_paccnt) 
  join clm_abon_tbl as a on (a.id = c.id_abon) 
) as ssss $qWhere;";
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
  select c.code, c.book, 
   (a.last_name||' '||coalesce(a.name,'')||' '||coalesce(a.patron_name,''))::varchar as abon, sss.* 
  from 
  (select * from 
  (select n.*, m.name as type_meter,i2.value as p_indic,
   CASE WHEN n.id_ind is null and n.id_work is null THEN 1 ELSE 0 END as is_manual,
   ph.num_pack, 
   find_prev_indic_fun(n.id_paccnt,n.id_meter,n.id_zone,n.id_energy,n.dat_ind) as calc_prev,
   'Помилкові попередні показники'::varchar as error_text
   from acm_indication_tbl as n
   left join eqi_meter_tbl as m on (m.id = n.id_typemet)
   left join acm_indication_tbl as i2 on (i2.id = n.id_prev)
   left join ind_pack_data as pd on (n.id_ind = pd.id)
   left join ind_pack_header as ph on (pd.id_pack = ph.id_pack)
   where n.mmgg = $pmmgg
  ) as ss 
  where coalesce(id_prev,0) <> coalesce(calc_prev,0)
 union all 
  select n.*, m.name as type_meter,i2.value as p_indic,
   CASE WHEN n.id_ind is null and n.id_work is null THEN 1 ELSE 0 END as is_manual,
   ph.num_pack , null as calc_prev, 'Є показники після демонтування'::varchar as error_text 
   from acm_indication_tbl as n
   left join eqi_meter_tbl as m on (m.id = n.id_typemet)
   left join acm_indication_tbl as i2 on (i2.id = n.id_prev)
   left join ind_pack_data as pd on (n.id_ind = pd.id)
   left join ind_pack_header as ph on (pd.id_pack = ph.id_pack)
   join (select id_meter, max(dat_ind) as max_dat from acm_indication_tbl group by id_meter order by id_meter) as mm
    on (mm.id_meter = n.id_meter )
   where n.id_operation  = 5 and mm.max_dat > n.dat_ind and n.mmgg = $pmmgg

 union all 
   select n.*, m.name as type_meter,i2.value as p_indic,
   CASE WHEN n.id_ind is null and n.id_work is null THEN 1 ELSE 0 END as is_manual,
   ph.num_pack, 
   null as calc_prev, 'Попередні показники більше наступних'::varchar as error_text
   from acm_indication_tbl as n
   join eqi_meter_tbl as m on (m.id = n.id_typemet)
   join acm_indication_tbl as i2 on (i2.id = n.id_prev)
   left join ind_pack_data as pd on (n.id_ind = pd.id)
   left join ind_pack_header as ph on (pd.id_pack = ph.id_pack)
    where i2.value > n.value and (n.value_cons = 0 or n.value_cons>5000)
    and n.mmgg = $pmmgg

 union all 
   select n.*, m.name as type_meter,i2.value as p_indic,
   CASE WHEN n.id_ind is null and n.id_work is null THEN 1 ELSE 0 END as is_manual,
   ph.num_pack, 
   null as calc_prev, 'Не вказано зону або лічильник'::varchar as error_text
   from acm_indication_tbl as n
   left join eqi_meter_tbl as m on (m.id = n.id_typemet)
   left join ind_pack_data as pd on (n.id_ind = pd.id)
   left join ind_pack_header as ph on (pd.id_pack = ph.id_pack)
   left join acm_indication_tbl as i2 on (i2.id = n.id_prev)
    where (n.id_zone is null or n.id_meter is null)
    and n.mmgg = $pmmgg

  ) as sss
  join clm_paccnt_tbl as c on (c.id = sss.id_paccnt) 
  join clm_abon_tbl as a on (a.id = c.id_abon) 
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