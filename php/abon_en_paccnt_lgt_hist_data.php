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
$fildsArray =DbGetFieldsArray($Link,'lgm_abon_h');

$fildsArray['grp_lgt'] =   array('f_name'=>'grp_lgt','f_type'=>'character varying');
$fildsArray['calc_name'] =   array('f_name'=>'calc_name','f_type'=>'character varying');
$fildsArray['user_name_open'] =   array('f_name'=>'user_name_open','f_type'=>'character varying');
$fildsArray['user_name_close'] =   array('f_name'=>'user_name_close','f_type'=>'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$lg_id = $_POST['lg_id'];
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' id = '.$lg_id;


 $SQL = "select * from (
  select lg.*, i.name as  grp_lgt, cl.name as calc_name, u1.name::varchar as user_name_open, u2.name::varchar as user_name_close
 from lgm_abon_h as lg
 left join lgi_group_tbl as i on (i.id = lg.id_grp_lgt)
 left join lgi_calc_header_tbl as cl on (cl.id = lg.id_calc)
 left join syi_user as u1 on (u1.id = lg.id_person_open)
 left join syi_user as u2 on (u2.id = lg.id_person_close)
 ) as ss
  $qWhere Order by $sidx $sord ;";


$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link).$SQL );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  //$data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {

     foreach ($fildsArray as $fild) {
         $data['rows'][$i][$fild['f_name']] = $row[$fild['f_name']];
     }

    $i++;
 } 
 $count=$i;
 $data['records'] = $count;

}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>