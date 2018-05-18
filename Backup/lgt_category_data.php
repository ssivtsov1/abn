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

$fildsArray =DbGetFieldsArray($Link,'lgi_kategor_tbl');

$qWhere= DbBuildWhere($_POST,$fildsArray);

/*
$Query="SELECT COUNT(*) AS count FROM lgi_kategor_tbl $qWhere;";
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
*/
$SQL = "select k.*  , lvl - 1 as level_field ,
        CASE WHEN (select count(*) from lgi_kategor_tbl where id_parent = k.id) = 0 THEN true 
             ELSE false END as leaf_field
        from lgi_kategor_tbl as k
          $qWhere Order by sort";


//        ,to_char(per_min, 'DD.MM.YYYY') as per_min
//        ,to_char(per_max, 'DD.MM.YYYY') as per_max

//throw new Exception(json_encode($SQL));

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
 // $data['page'] = $page;
  //$data['total'] = $total_pages;
  //$data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {
/*
      $data['rows'][$i]['id'] = $row['id'];
      $data['rows'][$i]['id_parrent'] = $row['id_parent'];
      $data['rows'][$i]['id_doc'] = $row['id_doc'];
      $data['rows'][$i]['lvl'] = $row['lvl'];
      
      $data['rows'][$i]['name'] = $row['name'];
      $data['rows'][$i]['ident'] = $row['ident'];

      $data['rows'][$i]['dt_b'] = $row['dt_b'];
      $data['rows'][$i]['dt_e'] = $row['dt_e'];
      
      $data['rows'][$i]['dt'] = $row['dt'];

      $data['rows'][$i]['level_field'] = $row['level_field'];
      $data['rows'][$i]['parent_id_field'] = $row['id_parent'];
      $data['rows'][$i]['leaf_field'] = true;
      $data['rows'][$i]['expanded_field'] = true;
      $data['rows'][$i]['loaded'] = true;
      */

      $data['rows'][$i]['cell'][] = $row['id'];
      $data['rows'][$i]['cell'][] = $row['id_parent'];
      $data['rows'][$i]['cell'][] = $row['id_doc'];
      $data['rows'][$i]['cell'][] = $row['lvl'];
      
      $data['rows'][$i]['cell'][] = $row['name'];
      $data['rows'][$i]['cell'][] = $row['ident'];

      $data['rows'][$i]['cell'][] = $row['dt_b'];
      $data['rows'][$i]['cell'][] = $row['dt_e'];
      
      $data['rows'][$i]['cell'][] = $row['dt'];

      $data['rows'][$i]['cell'][] = $row['level_field'];
      $data['rows'][$i]['cell'][] = $row['id_parent'];
      
      if ($row['leaf_field']=='t')
        $data['rows'][$i]['cell'][] =  true;
      else
        $data['rows'][$i]['cell'][] =  false;   
      
      $data['rows'][$i]['cell'][] = true;
      $data['rows'][$i]['cell'][] = true;

    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>