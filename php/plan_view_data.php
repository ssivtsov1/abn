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
$p_mmgg = $_GET['date'];
$p_cntr="'".$_GET['cntr']."'"; 

//$f=fopen('aaa_data.dat','w+');
//fputs($f,$p_mmgg);

$year_n = substr($p_mmgg,6,4);
$month_n = substr($p_mmgg,3,2);
 // get how many rows we want to have into the grid

if(!$sidx) $sidx =2; 
if(!$limit) $limit = 500;
if(!$page) $page = 1;

// connect to the database
//$fildsArray =DbGetFieldsArray($Link,'acm_bill_tbl');
//$fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
//$fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
//$fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
//$fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');

//$qWhere= DbBuildWhere($_POST,$fildsArray);

$Query="SELECT COUNT(*) AS count FROM act_plan_cache_tbl where year=".$year_n." and month=".$month_n.
        " and cntr=".$p_cntr;

$f=fopen('aaa_data.dat','w+');
fputs($f,$Query);
//throw new Exception(json_encode($Query));

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

$SQL = "select *,(select sum(days) from act_plan_cache_tbl where year="
         .$year_n." and month=".$month_n.
        " and cntr=".$p_cntr.") as total,cast(regexp_replace(sector, ' .+', '') as int) as tt"." from act_plan_cache_tbl "
        . "where year=".$year_n." and month=".$month_n.
        " and cntr=".$p_cntr.
        " order by "."cast(regexp_replace(sector, ' .+', '') as int)".
        " LIMIT $limit OFFSET $start";

$f=fopen('aaa_data.dat','w+');
fputs($f,$SQL);

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link).$SQL );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0; 
  while($row = pg_fetch_array($result)) {
      $data['rows'][$i]['cell'][] = $row['year'];
      $data['rows'][$i]['cell'][] = $row['month'];
      $data['rows'][$i]['cell'][] = $row['sector'];     
      $data['rows'][$i]['cell'][] = $row['days'];      
      $data['rows'][$i]['cell'][] = $row['total']; 
      $data['rows'][$i]['cell'][] = $row['tt'];
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>