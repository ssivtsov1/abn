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

$fildsArray =DbGetFieldsArray($Link,'eqm_eqp_tree_h');

//$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_eqp = $_POST['p_id'];
//if ($qWhere!='') $qWhere=$qWhere.' and ';
//else $qWhere=' where ';

$qWhere=' where code_eqp = '.$pid_eqp;

$Query="SELECT COUNT(*) AS count FROM eqm_eqp_tree_h $qWhere;";
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

$SQL = "select tt.id_key, tt.id_tree, tt.code_eqp_e, ep.name_eqp as name_eqp_e, tt.lvl,
       tt.dt_b, tt.dt_e, u1.name as user_name,   tt.mmgg, tt.dt 
       from eqm_eqp_tree_h as tt 
       left join  eqm_equipment_h as ep on (tt.code_eqp_e = ep.id )
       left join syi_user as u1 on (u1.id = tt.id_user)
       $qWhere  and ( ep.dt_b = (select max(dt_b) from eqm_equipment_h as e2 
       where e2.id = ep.id ) or ep.dt_b is null)
Order by $sidx $sord LIMIT $limit OFFSET $start ";

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {

      $data['rows'][$i]['cell'][] = $row['id_key'];
      $data['rows'][$i]['cell'][] = $row['id_tree'];
      $data['rows'][$i]['cell'][]=  $row['code_eqp_e'];
      $data['rows'][$i]['cell'][] = $row['name_eqp_e'];
      $data['rows'][$i]['cell'][] = $row['lvl'];      
      
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