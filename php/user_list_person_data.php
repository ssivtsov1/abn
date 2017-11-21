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

$fildsArray =DbGetFieldsArray($Link,'syi_user');
$fildsArray['represent_name'] =   array('f_name'=>'represent_name','f_type'=>'character varying');


$pid_grp = $_POST['p_id'];
$pid_person = $_POST['person_id'];

if ($pid_person!=0)
{
    $qWhere=" where id_parent = (select id_parent from syi_user where id = $pid_person) ";
}
else
{
    $qWhere= DbBuildWhere($_POST,$fildsArray);    
    if ($qWhere!='') $qWhere=$qWhere.' and ';
    else $qWhere=' where ';
    $qWhere=$qWhere.' id_parent = '.$pid_grp;
    
}


$Query="SELECT COUNT(*) AS count FROM syi_user $qWhere;";
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

$SQL = "select u.* , pr.represent_name 
        from syi_user as u 
        left join prs_persons as pr on (pr.id = u.id_person)
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
 
     foreach ($fildsArray as $fild) {
         $data['rows'][$i][$fild['f_name']] = $row[$fild['f_name']];
     }
     
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>