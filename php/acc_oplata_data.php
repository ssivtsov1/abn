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

$table_n = 'acm_headpay_tbl';
// get how many rows we want to have into the grid

if (!$sidx)
  $sidx = 2;
if (!$limit)
  $limit = 500;
if (!$page)
  $page = 1;

// connect to the database
$fildsArray = DbGetFieldsArray($Link, $table_n);

$qWhere = DbBuildWhere($_POST, $fildsArray);

$Query = "SELECT COUNT(*) AS count FROM $table_n $qWhere;";
$result = pg_query($Link, $Query) or die("SQL Error: " . pg_last_error($Link));
$row = pg_fetch_array($result);
$count = $row['count'];

//print("<br> count =  $count "); 

if ($count > 0 && $limit > 0) {
  $total_pages = ceil($count / $limit);
} else {
  $total_pages = 0;
}

if ($page > $total_pages)
  $page = $total_pages;

$start = $limit * $page - $limit; // do not put $limit*($page - 1)
if ($start < 0)
  $start = 0;

$SQL = "SELECT id_head,dt,id_person,reg_num,reg_date,mfo,name_file FROM $table_n
  $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start "; // WHERE del='f'

$result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link));
if (!$result) {
  print("<br> no records found");
} else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0;
  while ($row = pg_fetch_array($result)) {
    $data['rows'][$i]['cell'][] = $row['id_head'];
    $data['rows'][$i]['cell'][] = $row['name_file'];
    $data['rows'][$i]['cell'][] = $row['id_person'];
    $data['rows'][$i]['cell'][] = $row['dt'];
    $data['rows'][$i]['cell'][] = $row['reg_num'];
    $data['rows'][$i]['cell'][] = $row['reg_date'];
    $data['rows'][$i]['cell'][] = $row['mfo'];
    $i++;
  }
}

/*
insert into acm_headpay_tbl VALUES (nextval('acm_headpay_seq'), 
  now(), 5, 'JSDF13223', now(), 231111, '99322', 3125, 6252)
*/

header("Content-type: application/json;charset=utf-8");
echo json_encode($data);
?>