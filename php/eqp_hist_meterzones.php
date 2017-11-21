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

// connect to the database
$fildsArray =DbGetFieldsArray($Link,'clm_meter_zone_h');
$fildsArray['user_name_open'] =   array('f_name'=>'user_name_open','f_type'=>'character varying');
$fildsArray['user_name_close'] =   array('f_name'=>'user_name_close','f_type'=>'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_eqp = $_POST['m_id'];
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' id_meter = '.$pid_eqp;

/*
$Query="SELECT COUNT(*) AS count FROM clm_meter_zone_h $qWhere;";
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
$SQL = "select h.*, u1.name as user_name_open, u2.name as user_name_close
    from clm_meter_zone_h as h
    left join syi_user as u1 on (u1.id = h.id_person_open)
    left join syi_user as u2 on (u2.id = h.id_person_close)
  $qWhere Order by $sidx $sord ; ";

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link).$SQL );

 if ($result) 
 {
  $total_pages =1;
  $data['page'] = $page;
  $data['total'] = $total_pages;


  $i = 0; 
 while($row = pg_fetch_array($result)) {

     foreach ($fildsArray as $fild) {
         $data['rows'][$i][$fild['f_name']] = $row[$fild['f_name']];
     }

    $i++;
 } 
  $count =$i;
  $data['records'] = $count;
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>