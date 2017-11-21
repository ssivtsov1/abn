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

$fildsArray['num_meter'] =   array('f_name'=>'num_meter','f_type'=>'character varying');
$fildsArray['zone'] =   array('f_name'=>'zone','f_type'=>'character varying');
$fildsArray['tar_name'] =   array('f_name'=>'tar_name','f_type'=>'character varying');
$fildsArray['koef'] =   array('f_name'=>'koef','f_type'=>'numeric');
$fildsArray['value'] =   array('f_name'=>'value','f_type'=>'numeric');
$fildsArray['demand'] =   array('f_name'=>'demand','f_type'=>'numeric');
$fildsArray['demand_add'] =   array('f_name'=>'demand_add','f_type'=>'numeric');
$fildsArray['summ'] =   array('f_name'=>'summ','f_type'=>'numeric');
$fildsArray['dat_b'] =   array('f_name'=>'dat_b','f_type'=>'date');
$fildsArray['dat_e'] =   array('f_name'=>'dat_e','f_type'=>'date');

//$qWhere= DbBuildWhere($_POST,$fildsArray);

$id_doc = $_POST['id_doc'];
/*
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' id = '.$lg_id;
*/

 $SQL = "select * from (
select bs.id_paccnt, bs.id_zone, z.nm as zone, z.koef,   bs.demand, bs.demand_add, bs.summ ,bs.dat_b, bs.dat_e, m.num_meter, t.name as tar_name, td.value, t.ident
        from acm_bill_tbl as b 
        join acm_summ_tbl as bs on (bs.id_doc = b.id_doc)
        join eqk_zone_tbl as z on (z.id = bs.id_zone)
	join clm_meterpoint_h as m on (m.id_paccnt = b.id_paccnt)
        join aqm_tarif_tbl as t on (t.id = bs.id_tarif) 
	join aqd_tarif_tbl as td on (td.id = bs.id_summtarif)
        where b.id_doc = $id_doc 
        and m.dt_b = (select max(dt_b) from clm_meterpoint_h 
        where id = m.id and ((dt_b <= bs.dat_b) and (dt_e is null or dt_e >bs.dat_b)) )
        order by id_zone,t.ident 
 ) as ss
 Order by $sidx $sord ;";


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