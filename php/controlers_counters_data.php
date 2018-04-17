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
$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$p_mmgg = $row['mmgg'];
$p_mmgg = $_GET['date'];


//$p_mmgg = trim(sql_field_val('dt_b', 'mmgg'));

//$f=fopen('aaa_data.dat','w+');
//fputs($f,$p_mmgg);

$year_n = substr($p_mmgg,6,4);
$month_n = substr($p_mmgg,3,2);

if($month_n<>'1') {
        $month_n = intval($month_n)-1;
        $year_n = intval($year_n);
    }
    else{
        $year_n = intval($year_n)-1;
        $month_n=12;
    }    

$param_date = "'".$year_n.'-'.$month_n.'-01'."'";
//$pp_person = $_POST['person'];
$pp_person = '';

//$SQL="SELECT count(*) as count from job_counter_detail(".$param_date.",'".$pp_person."'".')';
$SQL="SELECT count(*) as count from job_counter_detail()";
file_put_contents('aaa_data.dat', $SQL);
//$f=fopen('aaa_data.dat','w+');
//fputs($f,$SQL);

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link).$SQL );
$row = pg_fetch_array($result);
$count = $row['count'];
//$count =count($row);

//print("<br> count =  $count "); 

if( $count >0  && $limit > 0) {
	$total_pages = ceil($count/$limit);

} else {
	$total_pages = 0;
}
$start = $limit*$page - $limit; // do not put $limit*($page - 1)
if($start < 0) $start = 0; 

if ($page > $total_pages) $page=$total_pages; 
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;
  
// $SQL="SELECT a.*,b.cntr from job_counter_detail(".$param_date.",'".$pp_person."'".') as a'.
//         " left join act_plan_cache_tbl b on trim(a.sector)=trim(b.sector)".
//         " order by cast(regexp_replace(a.sector, ' .+', '') as int) ".
//         " LIMIT $limit OFFSET $start"; 
 $SQL="SELECT a.*,b.cntr from job_counter_detail() as a ".
         " left join act_plan_cache_tbl b on trim(a.sector)=trim(b.sector)".
         " order by cast(regexp_replace(a.sector, ' .+', '') as int) ".
         " LIMIT $limit OFFSET $start"; 
 $result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link).$SQL );
 
  $i = 0; 
 while($row = pg_fetch_array($result)) {
      $data['rows'][$i]['cell'][] = $row['sector'];     
      $data['rows'][$i]['cell'][] = $row['cntr'];           
      $data['rows'][$i]['cell'][] = $row['place1'];
      $data['rows'][$i]['cell'][] = $row['place2'];
      $data['rows'][$i]['cell'][] = $row['place3'];      
      $data['rows'][$i]['cell'][] = $row['place4'];
      $data['rows'][$i]['cell'][] = $row['place5'];
      $data['rows'][$i]['cell'][] = $row['place6'];
      $data['rows'][$i]['cell'][] = $row['cost1'];
      $data['rows'][$i]['cell'][] = $row['cost2'];
      $data['rows'][$i]['cell'][] = $row['cost3'];
      $data['rows'][$i]['cell'][] = $row['cost4'];                  
      $data['rows'][$i]['cell'][] = $row['cost5'];                  
      $data['rows'][$i]['cell'][] = $row['cost6'];                  
      $data['rows'][$i]['cell'][] = $row['cost_all'];
    $i++;
 } 

header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>