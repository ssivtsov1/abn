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

// connect to the database
$fildsArray =DbGetFieldsArray($Link,'clm_meterpoint_tbl');

$fildsArray['typecompa'] =   array('f_name'=>'typecompa','f_type'=>'character varying');
$fildsArray['typecompu'] =   array('f_name'=>'typecompu','f_type'=>'character varying');
$fildsArray['type_meter'] =  array('f_name'=>'type_meter','f_type'=>'character varying');
$fildsArray['phase_meter'] =  array('f_name'=>'phase_meter','f_type'=>'int');
$fildsArray['station'] =     array('f_name'=>'station','f_type'=>'character varying');
$fildsArray['is_indic'] =     array('f_name'=>'is_indic','f_type'=>'int');

$qWhere="";
//$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_paccnt = $_POST['p_id'];
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' id_paccnt = '.$pid_paccnt;

$pfree = $_POST['free_only'];
if ($pfree==1)
{
  if ($qWhere!='') $qWhere=$qWhere.' and ';
  else $qWhere=' where ';

  $qWhere=$qWhere.' code_eqp is null ';
}

$hist_mode = $_POST['hist_mode'];
if ($hist_mode==1)
{
 $SQL = "select * from (
 select mp.*, ca.name as typecompa,  cu.name as typecompu, m.name as type_meter, 
    CASE WHEN m.phase = 1 THEN 1 WHEN m.phase = 2 THEN 3 END as phase_meter , 
       tp.name as station , 0 as is_indic
 from (
    select h1.dt_b, CASE WHEN h1.dt_e = '2099-01-01' THEN null else h1.dt_e END as dt_e,
    h2.id, h2.id_paccnt, h2.id_station, h2.code_eqp, h2.power, h2.calc_losts, h2.id_extra, 
       h2.smart, h2.magnet, h2.id_type_meter, h2.num_meter, h2.carry, h2.dt_control, h2.id_typecompa, 
       h2.dt_control_ca, h2.id_typecompu, h2.dt_control_cu, h2.coef_comp,dt_open as dt_input 
    from
    ( select id,min(dt_b) as dt_b,max(coalesce(dt_e,'2099-01-01')) as dt_e from clm_meterpoint_h 
      $qWhere group by id 
       order by id ) as h1
    join clm_meterpoint_h as h2 on (h1.id = h2.id and h1.dt_b = h2.dt_b)
    )
    as mp
 left join eqi_meter_tbl as m on (m.id = mp.id_type_meter)
 left join eqi_compensator_i_tbl as ca on (ca.id = mp.id_typecompa)
 left join eqi_compensator_i_tbl as cu on (cu.id = mp.id_typecompu)
 left join eqm_tp_tbl as tp on (tp.id = mp.id_station)
 ) as ss
  Order by dt_b ; ";

  $fildsArray['dt_e'] =     array('f_name'=>'dt_e','f_type'=>'date');    
}
else
{
$SQL = "select * from (
 select mp.*, ca.name as typecompa,  cu.name as typecompu, m.name as type_meter,
       tp.name as station , CASE WHEN m.phase = 1 THEN 1 WHEN m.phase = 2 THEN 3 END as phase_meter , 
       CASE WHEN ind_meter.id_meter is not null THEN 1 ELSE 0 END as is_indic
from clm_meterpoint_tbl as mp
 left join eqi_meter_tbl as m on (m.id = mp.id_type_meter)
 left join eqi_compensator_i_tbl as ca on (ca.id = mp.id_typecompa)
 left join eqi_compensator_i_tbl as cu on (cu.id = mp.id_typecompu)
 left join eqm_tp_tbl as tp on (tp.id = mp.id_station)
 left join (select distinct id_meter from acm_indication_tbl) as ind_meter  
     on (ind_meter.id_meter = mp.id)
) as ss
  $qWhere Order by $sidx $sord ; ";
    
}
/*
$Query="SELECT COUNT(*) AS count FROM clm_meterpoint_tbl $qWhere;";
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
*/
$start = 0; 
$total_pages = 1;


$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link).$SQL );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['userdata']['lmetersselect'] = DbTableSelect($Link," (select distinct id,num_meter from clm_meterpoint_h where id_paccnt = $pid_paccnt) as ss ",'id','num_meter');
  
  $i = 0; 
 while($row = pg_fetch_array($result)) {

     foreach ($fildsArray as $fild) {
         $data['rows'][$i][$fild['f_name']] = $row[$fild['f_name']];
     }

    $i++; 
 }
 $count=$i;
 $data['records'] = $count;
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>