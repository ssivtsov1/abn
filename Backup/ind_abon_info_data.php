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
$fildsArray = array();
$fildsArray['mmgg'] = array('f_name' => 'mmgg', 'f_type' => 'date');
$fildsArray['demand'] = array('f_name' => 'demand', 'f_type' => 'integer');
$fildsArray['source'] =   array('f_name'=>'source','f_type'=>'character varying');

//$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_paccnt = $_POST['p_id'];
$pid_zone = $_POST['p_id_zone'];
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere." id_paccnt = $pid_paccnt and id_zone = $pid_zone ";


$Query="SELECT COUNT(*) AS count FROM (
 select ss.*, it.name as source
 from
 (
  select  mmgg, sum(value_cons)::int as demand , min(CASE WHEN id_operation <>23 THEN id_operation END ) as operation
  from  acm_indication_tbl 
  $qWhere
  group by mmgg
 ) as ss
left join cli_indic_type_tbl as it on (ss.operation = it.id)
order by mmgg desc
) as sss ";
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
 select ss.*, it.name as source 
 from
 (
  select  mmgg, sum(value_cons)::int as demand , min(CASE WHEN id_operation <>23 THEN id_operation END ) as operation
  from  acm_indication_tbl 
  $qWhere
  group by mmgg
 ) as ss
left join cli_indic_type_tbl as it on (ss.operation = it.id)
order by mmgg desc
) as ss
   LIMIT $limit OFFSET $start ";

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