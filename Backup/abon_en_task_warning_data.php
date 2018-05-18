<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa"); 
session_start();

error_reporting(1);

$Link = get_database_link($_SESSION,1);
$session_user = $_SESSION['ses_usr_id'];
$id_session = $_SESSION['id_sess'];
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
$fildsArray = array();
$fildsArray['id'] = array('f_name' => 'id', 'f_type' => 'integer');
$fildsArray['id_paccnt'] = array('f_name' => 'id_paccnt', 'f_type' => 'integer');
$fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
$fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
$fildsArray['town'] = array('f_name' => 'town', 'f_type' => 'character varying');
$fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
$fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
$fildsArray['sector_name'] = array('f_name' => 'sector_name', 'f_type' => 'character varying');
$fildsArray['id_region'] = array('f_name' => 'id_region', 'f_type' => 'int');
$fildsArray['dt_action'] = array('f_name' => 'dt_action', 'f_type' => 'date');
$fildsArray['sum_warning'] = array('f_name' => 'sum_warning', 'f_type' => 'numeric');
$fildsArray['dt_warning'] = array('f_name' => 'dt_warning', 'f_type' => 'date');
$fildsArray['sum_delta'] = array('f_name' => 'sum_delta', 'f_type' => 'numeric');
$fildsArray['debet_now'] = array('f_name' => 'debet_now', 'f_type' => 'numeric');
$fildsArray['note'] = array('f_name' => 'note', 'f_type' => 'character varying');
$fildsArray['ftask'] = array('f_name' => 'ftask', 'f_type' => 'int');

$qWhere= DbBuildWhere($_POST,$fildsArray);
/*
$pid_eqp = $_POST['p_id'];
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' id_paccnt = '.$pid_eqp;
*/

$Query="select tmp_warning_debet_fun(fun_mmgg(),$id_session);";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );


$Query="SELECT COUNT(*) AS count FROM ( 
  select * from tmp_warning_debet_tbl where id_session = $id_session ) as sss
$qWhere;";
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
  select * from tmp_warning_debet_tbl where id_session = $id_session 
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