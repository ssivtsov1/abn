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

$fildsArray =DbGetFieldsArray($Link,'eqm_equipment_h');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_eqp = $_POST['p_id'];
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' eq.id = '.$pid_eqp;

$Query="SELECT COUNT(*) AS count FROM eqm_equipment_h as eq $qWhere;";
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

$SQL = "select eq.id_key,eq.id, eq.name_eqp, eq.num_eqp,  eq.id_addr as addr, eq.loss_power, eq.is_owner,
       eq.dt_install, eq.dt_change, eq.dt_b, eq.dt_e, u1.name as user_name, eq.mmgg, eq.dt 
       from eqm_equipment_h as eq
       left join syi_user as u1 on (u1.id = eq.id_user)
      $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";

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
      $data['rows'][$i]['cell'][] = $row['name_eqp'];
      $data['rows'][$i]['cell'][]=  $row['num_eqp'];
      $data['rows'][$i]['cell'][] = $row['addr'];
      $data['rows'][$i]['cell'][] = $row['loss_power'];      
      $data['rows'][$i]['cell'][] = $row['is_owner'];
      $data['rows'][$i]['cell'][] = $row['dt_install'];      
      $data['rows'][$i]['cell'][] = $row['dt_change'];            
      
      $data['rows'][$i]['cell'][] = $row['dt_b'];                  
      $data['rows'][$i]['cell'][] = $row['dt_e'];                  
      $data['rows'][$i]['cell'][] = $row['user_name'];                  
      $data['rows'][$i]['cell'][] = $row['mmgg'];                  
      $data['rows'][$i]['cell'][] = $row['dt'];                        
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>