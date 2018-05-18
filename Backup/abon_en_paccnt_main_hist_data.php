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
//$fildsArray =DbGetFieldsArray($Link,'lgm_abon_h');

$fildsArray['id'] = array('f_name' => 'id', 'f_type' => 'integer');
$fildsArray['id_key'] = array('f_name' => 'id_key', 'f_type' => 'integer');
$fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
$fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
$fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
$fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
$fildsArray['note'] = array('f_name' => 'note', 'f_type' => 'character varying');

$fildsArray['archive'] = array('f_name' => 'archive', 'f_type' => 'integer');
$fildsArray['activ'] = array('f_name' => 'activ', 'f_type' => 'integer');
$fildsArray['rem_worker'] = array('f_name' => 'rem_worker', 'f_type' => 'integer');
$fildsArray['not_live'] = array('f_name' => 'not_live', 'f_type' => 'integer');
$fildsArray['pers_cntrl'] = array('f_name' => 'pers_cntrl', 'f_type' => 'integer');
$fildsArray['green_tarif'] = array('f_name' => 'green_tarif', 'f_type' => 'integer');
$fildsArray['cntrl'] = array('f_name' => 'cntrl', 'f_type' => 'character varying');
$fildsArray['gtar'] = array('f_name' => 'gtar', 'f_type' => 'character varying');
$fildsArray['house_kind'] = array('f_name' => 'house_kind', 'f_type' => 'character varying');

$fildsArray['n_subs'] = array('f_name' => 'n_subs', 'f_type' => 'character varying');
$fildsArray['heat_area'] = array('f_name' => 'heat_area', 'f_type' => 'numeric');

$fildsArray['dt_b'] = array('f_name' => 'dt_b', 'f_type' => 'date');
$fildsArray['dt_e'] = array('f_name' => 'dt_e', 'f_type' => 'date');

$fildsArray['period_open'] = array('f_name' => 'period_open', 'f_type' => 'date');
$fildsArray['period_close'] = array('f_name' => 'period_close', 'f_type' => 'date');

$fildsArray['dt_open'] = array('f_name' => 'dt_open', 'f_type' => 'date');
$fildsArray['dt_close'] = array('f_name' => 'dt_close', 'f_type' => 'date');

$fildsArray['user_name_open'] = array('f_name' => 'user_name_open', 'f_type' => 'character varying');
$fildsArray['user_name_close'] = array('f_name' => 'user_name_close', 'f_type' => 'character varying');


$qWhere= DbBuildWhere($_POST,$fildsArray);

$p_id = $_POST['p_id'];
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' acc.id = '.$p_id;

/*
$Query="SELECT COUNT(*) AS count FROM lgm_abon_tbl $qWhere;";
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

$SQL = "select acc.id, acc.id_key, acc.book, acc.code, acc.id_abon, 
    address_print_full(acc.addr,4) as addr,   acc.note, 
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
  acc.id_cntrl, cn.represent_name as cntrl, acc.id_gtar, tar.sh_nm as gtar,
  acc.n_subs , acc.idk_house, ht.name as house_kind,
  acc.activ::int, acc.rem_worker::int, acc.not_live::int, acc.pers_cntrl::int ,acc.archive::int, acc.green_tarif::int,
  acc.dt_b, acc.period_open, acc.dt_open, acc.id_person_open, acc.dt_e,  acc.period_close, acc.dt_close, acc.id_person_close,
  acc.heat_area,
  u1.name::varchar as user_name_open, u2.name::varchar as user_name_close
from clm_paccnt_h as acc 
left join clm_abon_tbl as c on (c.id = acc.id_abon) 
left join prs_persons as cn on (cn.id = acc.id_cntrl)     
left join aqi_grptar_tbl as tar on (tar.id = acc.id_gtar)         
left join cli_house_type_tbl as ht on (ht.id = acc.idk_house)         
left join syi_user as u1 on (u1.id = acc.id_person_open)
left join syi_user as u2 on (u2.id = acc.id_person_close) 

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