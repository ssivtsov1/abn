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
//$fildsArray['name_abon'] =   array('f_name'=>'name_abon','f_type'=>'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);


$Query="SELECT COUNT(*) AS count FROM (
    select pr.* , p.name as name_post 
        from prs_persons as pr 
        left join prs_posts as p on (p.id = pr.id_post)
) as ss $qWhere;";
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

$SQL = "select * from (select pr.* , p.name as name_post 
        from prs_persons as pr 
        left join prs_posts as p on (p.id = pr.id_post) 
     ) as ss
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
      $data['rows'][$i]['cell'][] = $row['id_post'];      
      $data['rows'][$i]['cell'][] = $row['name_post'];     
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>