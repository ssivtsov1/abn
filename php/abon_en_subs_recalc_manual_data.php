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
//$fildsArray =DbGetFieldsArray($Link,'acm_subs_tbl');
$fildsArray['id'] =   array('f_name'=>'id','f_type'=>'integer');
$fildsArray['id_manual'] =   array('f_name'=>'id_manual','f_type'=>'integer');
$fildsArray['id_paccnt'] =   array('f_name'=>'id_paccnt','f_type'=>'integer');

$fildsArray['subs_current'] =   array('f_name'=>'subs_current','f_type'=>'numeric');
$fildsArray['bill_sum'] =   array('f_name'=>'bill_sum','f_type'=>'numeric');

$fildsArray['ob_pay'] =   array('f_name'=>'ob_pay','f_type'=>'numeric');
$fildsArray['mmgg_calc'] =   array('f_name'=>'mmgg_calc','f_type'=>'date');
$fildsArray['mmgg'] =   array('f_name'=>'mmgg','f_type'=>'date');

$fildsArray['enabled'] =   array('f_name'=>'enabled','f_type'=>'integer');
$fildsArray['source'] =   array('f_name'=>'source','f_type'=>'varchar');


$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_paccnt = $_POST['p_id'];
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' id_paccnt = '.$pid_paccnt;


$Query="SELECT COUNT(*) AS count FROM 
(
 select r.id,r.id_paccnt,rm.id as id_manual, 
 r.mmgg_calc,
 COALESCE(rm.subs_current,r.subs_current) as subs_current,
 COALESCE(rm.ob_pay,r.ob_pay) as ob_pay,
 COALESCE(rm.bill_sum,r.bill_sum) as bill_sum, r.enabled,
 CASE WHEN rm.id is not null then 'Скориговані'
      WHEN r.source =1 then 'Абон-Енерго'
      WHEN r.source =2 then 'МСМ'
      END as source   
 from 
 rep_subs_recalc_month_tbl as r
 left join rep_subs_recalc_month_manual_tbl as rm on (r.mmgg_calc = rm.mmgg_calc and r.id_paccnt = rm.id_paccnt)
) as ss $qWhere;";
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

$SQL = "select * from (
 select r.id,r.id_paccnt,rm.id as id_manual, r.enabled,
 r.mmgg_calc,
 COALESCE(rm.subs_current,r.subs_current) as subs_current,
 COALESCE(rm.ob_pay,r.ob_pay) as ob_pay,
 COALESCE(rm.bill_sum,r.bill_sum) as bill_sum,
 CASE WHEN rm.id is not null then 'Скориговані'
      WHEN r.source =1 then 'Абон-Енерго'
      WHEN r.source =2 then 'МСМ'
      END as source   
 from 
 rep_subs_recalc_month_tbl as r
 left join rep_subs_recalc_month_manual_tbl as rm on (r.mmgg_calc = rm.mmgg_calc and r.id_paccnt = rm.id_paccnt)
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
      $data['rows'][$i]['cell'][] = $row['id_manual'];
      $data['rows'][$i]['cell'][] = $row['id_paccnt'];
      $data['rows'][$i]['cell'][] = $row['mmgg_calc'];
      $data['rows'][$i]['cell'][]=  $row['subs_current'];
      $data['rows'][$i]['cell'][] = $row['ob_pay'];
      $data['rows'][$i]['cell'][] = $row['bill_sum'];      
      $data['rows'][$i]['cell'][] = $row['source'];
      $data['rows'][$i]['cell'][] = $row['enabled'];
    $i++;
 } 
 
}

header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>