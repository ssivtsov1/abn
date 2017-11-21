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

$fildsArray =DbGetFieldsArray($Link,'prs_persons');
$fildsArray['name_post'] =   array('f_name'=>'name_post','f_type'=>'character varying');
$fildsArray['name_abon'] =   array('f_name'=>'name_abon','f_type'=>'character varying');


$pid_dep = $_POST['p_id'];
$pid_person = $_POST['person_id'];

if ($pid_person!=0)
{
    $qWhere=" where id_department = (select id_department from prs_persons where id = $pid_person) ";
}
else
{
    $qWhere= DbBuildWhere($_POST,$fildsArray);    
    if ($qWhere!='') $qWhere=$qWhere.' and ';
    else $qWhere=' where ';
    $qWhere=$qWhere.' id_department = '.$pid_dep;
    
} 


$Query="SELECT COUNT(*) AS count FROM prs_persons $qWhere;";
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

$SQL = "select pr.* , p.name as name_post, cl.last_name as name_abon, u1.name as user_name 
        from prs_persons as pr 
        left join clm_abon_tbl as cl on (cl.id = pr.id_abon)
        left join prs_posts as p on (p.id = pr.id_post)
        left join syi_user as u1 on (u1.id = pr.id_person)
          $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";

//throw new Exception(json_encode($SQL));

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {
      $data['rows'][$i]['cell'][] = $row['id'];
      $data['rows'][$i]['cell'][] = $row['id_department'];
      $data['rows'][$i]['cell'][] = $row['represent_name'];
      $data['rows'][$i]['cell'][] = $row['soname'];
      $data['rows'][$i]['cell'][] = $row['name'];
      $data['rows'][$i]['cell'][] = $row['father_name'];
      $data['rows'][$i]['cell'][] = $row['id_post'];      
      $data['rows'][$i]['cell'][] = $row['name_post'];     
      $data['rows'][$i]['cell'][] = $row['phone'];     
      $data['rows'][$i]['cell'][] = $row['id_abon'];      
      $data['rows'][$i]['cell'][] = $row['name_abon'];     
      
      $data['rows'][$i]['cell'][] = $row['is_active'];     
      $data['rows'][$i]['cell'][] = $row['is_runner'];     
      
      $data['rows'][$i]['cell'][] = $row['date_start'];     
      $data['rows'][$i]['cell'][] = $row['date_end'];     
      
      $data['rows'][$i]['cell'][] = $row['work_period'];                  
      $data['rows'][$i]['cell'][] = $row['dt_input'];                  
      $data['rows'][$i]['cell'][] = $row['user_name'];                  
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>