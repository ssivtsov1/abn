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

if ($sidx=='dat_ind') 
{ 
    if ($sord =="desc")
      $sidx="dat_ind $sord ,id_operation ASC";
    else
      $sidx="dat_ind $sord ,id_operation DESC";        
    $sord='';
}

// connect to the database
$fildsArray =DbGetFieldsArray($Link,'acm_indication_h');
$fildsArray['type_meter'] = array('f_name' => 'type_meter', 'f_type' => 'character varying');
$fildsArray['is_manual'] = array('f_name' => 'is_manual', 'f_type' => 'integer');
//$fildsArray['p_indic'] = array('f_name' => 'p_indic', 'f_type' => 'integer');
//$fildsArray['p_dat_ind'] = array('f_name' => 'p_dat_ind', 'f_type' => 'date');
//$fildsArray['num_pack'] = array('f_name' => 'num_pack', 'f_type' => 'character varying');
//$fildsArray['id_hwork'] = array('f_name' => 'id_hwork', 'f_type' => 'integer');
//$fildsArray['idk_work'] = array('f_name' => 'idk_work', 'f_type' => 'integer');
//$fildsArray['indic_real'] = array('f_name' => 'indic_real', 'f_type' => 'integer');
$fildsArray['user_name'] =   array('f_name'=>'user_name','f_type'=>'character varying');
$fildsArray['user_name_change'] =   array('f_name'=>'user_name_change','f_type'=>'character varying');


$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_ind = $_POST['p_id'];
$pid_paccnt = $_POST['pid_paccnt'];


if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

if ($pid_ind>0)
  $qWhere=$qWhere." id = $pid_ind ";
else
  $qWhere=$qWhere." id_paccnt = $pid_paccnt and not exists (select ii.id from acm_indication_tbl as ii where ii.id = ss.id ) "  ;


$Query="SELECT COUNT(*) AS count FROM (
 select n.*, m.name as type_meter,
 CASE WHEN id_ind is null and id_work is null THEN 1 ELSE 0 END as is_manual, u1.name as user_name ,
 CASE WHEN n.id_ind is null and n.id_work is null THEN 1 ELSE 0 END as is_manual,
 u2.name as user_name_change 
 from acm_indication_h as n
 join eqi_meter_tbl as m on (m.id = n.id_typemet)
 left join syi_user as u1 on (u1.id = n.id_person)
 left join syi_user as u2 on (u2.id = n.id_person_change)
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

$SQL = "select * from (
 select n.*, m.name as type_meter, -- i2.value as p_indic,i2.dat_ind as p_dat_ind,
 CASE WHEN n.id_ind is null and n.id_work is null THEN 1 ELSE 0 END as is_manual,
 u1.name as user_name , u2.name as user_name_change 
 from acm_indication_h as n
 join eqi_meter_tbl as m on (m.id = n.id_typemet)
 left join syi_user as u1 on (u1.id = n.id_person)
 left join syi_user as u2 on (u2.id = n.id_person_change)
) as ss
  $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";

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