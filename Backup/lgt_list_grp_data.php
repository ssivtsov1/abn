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

 if ($sidx=='ident')  $sidx="int_ident $sord, ident";

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

$fildsArray =DbGetFieldsArray($Link,'lgi_group_tbl');
$fildsArray['kategor'] =   array('f_name'=>'kategor','f_type'=>'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$Query="SELECT COUNT(*) AS count FROM (select g.* , k.name as kategor, c.name as calc_name, u1.name as user_name 
        from lgi_group_tbl as g 
        left join lgi_kategor_tbl as k on (k.id = g.id_kategor)
        left join lgi_calc_header_tbl as c on (c.id = g.id_calc)
        left join syi_user as u1 on (u1.id = g.id_person)) as ss $qWhere;";
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

$SQL = "select * from (select g.* , k.name as kategor, c.name as calc_name, u1.name as user_name ,
regexp_replace(regexp_replace(g.ident, '-.*?$', '') , '[^0-9]', '','g')::int as int_ident 
        from lgi_group_tbl as g 
        left join lgi_kategor_tbl as k on (k.id = g.id_kategor)
        left join lgi_calc_header_tbl as c on (c.id = g.id_calc)
        left join syi_user as u1 on (u1.id = g.id_person)) as ss
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
      $data['rows'][$i]['cell'][] = $row['ident'];
      $data['rows'][$i]['cell'][] = $row['alt_code'];
      $data['rows'][$i]['cell'][] = $row['kfk_code'];
      $data['rows'][$i]['cell'][] = $row['name'];
      $data['rows'][$i]['cell'][] = $row['bill_name'];
      $data['rows'][$i]['cell'][] = $row['id_kategor'];
      $data['rows'][$i]['cell'][] = $row['kategor'];
      $data['rows'][$i]['cell'][] = $row['id_budjet'];
      $data['rows'][$i]['cell'][] = $row['id_calc'];           
      $data['rows'][$i]['cell'][] = $row['calc_name'];           
      $data['rows'][$i]['cell'][] = $row['id_document'];      
      $data['rows'][$i]['cell'][] = $row['id_state'];     
      $data['rows'][$i]['cell'][] = $row['dt_b'];     
      $data['rows'][$i]['cell'][] = $row['dt_e'];     
      
      $data['rows'][$i]['cell'][] = $row['work_period'];                  
      $data['rows'][$i]['cell'][] = $row['dt'];                  
      $data['rows'][$i]['cell'][] = $row['user_name'];                  
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>