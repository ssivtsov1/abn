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

if ($sidx=='mmgg')  $sidx="mmgg $sord ,dt_b $sord ,dt_e $sord ";

// connect to the database
//$fildsArray =DbGetFieldsArray($Link,'acm_subs_tbl');
$fildsArray['id'] =   array('f_name'=>'id','f_type'=>'integer');
$fildsArray['id_paccnt'] =   array('f_name'=>'id_paccnt','f_type'=>'integer');
$fildsArray['sum_subs'] =   array('f_name'=>'sum_subs','f_type'=>'numeric');
$fildsArray['sum_recalc'] =   array('f_name'=>'sum_recalc','f_type'=>'numeric');
$fildsArray['ob_pay'] =   array('f_name'=>'ob_pay','f_type'=>'numeric');
$fildsArray['kol_subs'] =   array('f_name'=>'kol_subs','f_type'=>'integer');
$fildsArray['dt_b'] =   array('f_name'=>'dt_b','f_type'=>'date');
$fildsArray['dt_e'] =   array('f_name'=>'dt_e','f_type'=>'date');
$fildsArray['mmgg'] =   array('f_name'=>'mmgg','f_type'=>'date');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_eqp = $_POST['p_id'];
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' id_paccnt = '.$pid_eqp;


$Query="SELECT COUNT(*) AS count FROM 
(
 select id,id_paccnt,
 CASE WHEN coalesce(val_month,0) <> 0 THEN subs_all END  as  sum_subs,
 CASE WHEN coalesce(val_month,0) = 0  THEN subs_all END  as  sum_recalc,
 ob_pay,kol_subs , dt_b, dt_e, mmgg 
 from 
 acm_subs_tbl
) as ss $qWhere;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$count = $row['count'];

//print("<br> count =  $count "); 
// CASE WHEN val_month <> 0 THEN subs_month END  as  sum_subs,
 //CASE WHEN val_month = 0 THEN subs_all END  as  sum_recalc,


if( $count >0  && $limit > 0) { 
	$total_pages = ceil($count/$limit);

} else {
	$total_pages = 0;
}

if ($page > $total_pages) $page=$total_pages;

$start = $limit*$page - $limit; // do not put $limit*($page - 1)
if($start < 0) $start = 0; 

$SQL = "select * from (
 select id,id_paccnt,
 CASE WHEN coalesce(val_month,0) <> 0 THEN subs_all END  as  sum_subs,
 CASE WHEN coalesce(val_month,0) = 0  THEN subs_all END  as  sum_recalc,
 ob_pay,kol_subs , dt_b, dt_e, mmgg 
 from 
 acm_subs_tbl
) as ss
  $qWhere Order by $sidx LIMIT $limit OFFSET $start ";

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link).$SQL );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0; 

 while($row = pg_fetch_array($result)) {

      $data['rows'][$i]['cell'][] = $row['id'];
      $data['rows'][$i]['cell'][] = $row['id_paccnt'];
      $data['rows'][$i]['cell'][] = $row['mmgg'];
      $data['rows'][$i]['cell'][] = $row['dt_b'];
      $data['rows'][$i]['cell'][] = $row['dt_e'];
      $data['rows'][$i]['cell'][]=  $row['sum_subs'];
      $data['rows'][$i]['cell'][] = $row['sum_recalc'];
      $data['rows'][$i]['cell'][] = $row['ob_pay'];      
      $data['rows'][$i]['cell'][] = $row['kol_subs'];
    $i++;
 } 
 
}

header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>