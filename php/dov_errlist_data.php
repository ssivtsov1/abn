<?php
header("Content-type: application/json;charset=utf-8");

require 'abon_en_func.php';
require 'abon_ded_func.php';
	session_name("session_kaa");
	session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,1);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

$page = $_POST['page']; // get the requested page
$limit = $_POST['rows']; // get how many rows we want to have into the grid
$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
$sord = $_POST['sord']; // get the direction

 // get how many rows we want to have into the grid

if(!$sidx) $sidx =1;
if(!$limit) $limit = 10;
if(!$page) $page = 1;


$fildsArray =DbGetFieldsArray($Link,'sys_loaderror_tbl');

$qWhere= DbBuildWhere($_POST,$fildsArray);

// connect to the database

$Query="SELECT COUNT(*) AS count FROM sys_loaderror_tbl $qWhere";
$result = pg_query($Link,$Query);
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

$SQL = "select * from sys_loaderror_tbl $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";

$result = pg_query($Link,$SQL);
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {

      $data['rows'][$i]['cell'][] = $row['dt'];
      $data['rows'][$i]['cell'][]= $row['mmgg'];
      $data['rows'][$i]['cell'][] = $row['text'];
    $i++;
 } 
}

echo json_encode($data);

?>