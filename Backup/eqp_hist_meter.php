<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(1);

$Link = get_database_link($_SESSION);
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

$fildsArray =DbGetFieldsArray($Link,'clm_meterpoint_h');
$fildsArray['typecompa'] =   array('f_name'=>'typecompa','f_type'=>'character varying');
$fildsArray['typecompu'] =   array('f_name'=>'typecompu','f_type'=>'character varying');
$fildsArray['type_meter'] =  array('f_name'=>'type_meter','f_type'=>'character varying');
$fildsArray['station'] =     array('f_name'=>'station','f_type'=>'character varying');


$qWhere= DbBuildWhere($_POST,$fildsArray);

if (isset($_POST['p_id']))
{
  $pid_eqp = $_POST['p_id'];
  if ($qWhere!='') $qWhere=$qWhere.' and ';
  else $qWhere=' where ';

  $qWhere=$qWhere.' code_eqp = '.$pid_eqp;
}

if (isset($_POST['m_id']))
{
  $pid_meter = $_POST['m_id'];
  if ($qWhere!='') $qWhere=$qWhere.' and ';
  else $qWhere=' where ';

  $qWhere=$qWhere.' id = '.$pid_meter;
}

$Query="SELECT COUNT(*) AS count FROM clm_meterpoint_h $qWhere;";
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

$SQL = "select * from (select mp.*, ca.name as typecompa,  cu.name as typecompu, m.name as type_meter,
       tp.name as station ,u1.name as user_name_open, u2.name as user_name_close
from clm_meterpoint_h as mp
 left join eqi_meter_tbl as m on (m.id = mp.id_type_meter)
 left join eqi_compensator_i_tbl as ca on (ca.id = mp.id_typecompa)
 left join eqi_compensator_i_tbl as cu on (cu.id = mp.id_typecompu)
 left join eqm_tp_tbl as tp on (tp.id = mp.id_station)
 left join syi_user as u1 on (u1.id = mp.id_person_open)
 left join syi_user as u2 on (u2.id = mp.id_person_close)

 ) as ss   $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";

//throw new Exception(json_encode($SQL));

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages; 
  $data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {

      $data['rows'][$i]['cell'][] = $row['id_key'];
      $data['rows'][$i]['cell'][] = $row['id'];
      $data['rows'][$i]['cell'][] = $row['num_meter'];
      $data['rows'][$i]['cell'][] = $row['id_type_meter'];
      $data['rows'][$i]['cell'][] = $row['type_meter'];      
      $data['rows'][$i]['cell'][] = $row['carry'];      
      $data['rows'][$i]['cell'][] = $row['dt_control'];      
      $data['rows'][$i]['cell'][] = $row['dt_control_ca'];      
      $data['rows'][$i]['cell'][] = $row['dt_control_cu'];      
      $data['rows'][$i]['cell'][] = $row['power'];      
      $data['rows'][$i]['cell'][] = $row['id_typecompa'];      
      $data['rows'][$i]['cell'][] = $row['typecompa'];      
      $data['rows'][$i]['cell'][] = $row['id_typecompu'];      
      $data['rows'][$i]['cell'][] = $row['typecompu'];      
      $data['rows'][$i]['cell'][] = $row['coef_comp'];      
      $data['rows'][$i]['cell'][] = $row['station'];      
      $data['rows'][$i]['cell'][] = $row['calc_losts'];      
      $data['rows'][$i]['cell'][] = $row['smart'];      
      $data['rows'][$i]['cell'][] = $row['magnet'];      
      //$data['rows'][$i]['cell'][] = $row['dt_start'];            
      //$data['rows'][$i]['cell'][] = $row['dt_end'];                  
      
      
      $data['rows'][$i]['cell'][] = $row['dt_b'];                  
      $data['rows'][$i]['cell'][] = $row['dt_e'];                  
      $data['rows'][$i]['cell'][] = $row['period_open'];                  
      $data['rows'][$i]['cell'][] = $row['dt_open'];
      $data['rows'][$i]['cell'][] = $row['user_name_open'];
      $data['rows'][$i]['cell'][] = $row['period_close'];                  
      $data['rows'][$i]['cell'][] = $row['dt_close'];                        
      $data['rows'][$i]['cell'][] = $row['user_name_close'];

      $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>