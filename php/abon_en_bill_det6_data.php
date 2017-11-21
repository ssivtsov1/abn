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

$fildsArray['id'] =  array('f_name'=>'id','f_type'=>'integer');
$fildsArray['sn_len'] = array('f_name'=>'sn_len','f_type'=>'numeric');
$fildsArray['tt'] = array('f_name'=>'tt','f_type'=>'integer');
$fildsArray['tw'] = array('f_name'=>'tw','f_type'=>'integer');
$fildsArray['pxx_r0'] = array('f_name'=>'pxx_r0','f_type'=>'numeric');
$fildsArray['pkz_x0'] = array('f_name'=>'pkz_x0','f_type'=>'numeric');
$fildsArray['ixx'] = array('f_name'=>'ixx','f_type'=>'numeric');
$fildsArray['ukz_un'] = array('f_name'=>'ukz_un','f_type'=>'numeric');
$fildsArray['wp'] = array('f_name'=>'wp','f_type'=>'integer');
$fildsArray['wq'] = array('f_name'=>'wq','f_type'=>'integer');
$fildsArray['wp_summ'] = array('f_name'=>'wp_summ','f_type'=>'integer');
$fildsArray['s_xx_addwp'] = array('f_name'=>'s_xx_addwp','f_type'=>'integer');
$fildsArray['s_kz_addwq'] = array('f_name'=>'s_kz_addwq','f_type'=>'integer');
$fildsArray['dw'] = array('f_name'=>'dw','f_type'=>'integer');
$fildsArray['lvl'] = array('f_name'=>'lvl','f_type'=>'integer');

$fildsArray['name_eqp'] = array('f_name'=>'name_eqp','f_type'=>'varchar');
$fildsArray['kind'] = array('f_name'=>'kind','f_type'=>'varchar');
$fildsArray['type_name'] = array('f_name'=>'type_name','f_type'=>'varchar');
$fildsArray['voltage'] = array('f_name'=>'voltage','f_type'=>'numeric');

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
select ls.id, ls.sn_len, ls.tt, ls.tw, ls.pxx_r0, ls.pkz_x0, 
ls.ixx, ls.ukz_un, ls.wp, ls.wq, ls.wp_summ, ls.s_xx_addwp, ls.s_kz_addwq, ls.dw,  ls.dat_b, ls.dat_e, ls.lvl,
eq.name_eqp, k.name as kind, v.voltage,
CASE WHEN ls.type_eqp = 2 THEN (select name from eqi_compensator_tbl where id = ls.id_type_eqp limit 1)
     WHEN ls.type_eqp = 6 THEN (select name from eqi_cable_tbl where id = ls.id_type_eqp limit 1)
     WHEN ls.type_eqp = 7 THEN (select name from eqi_corde_tbl where id = ls.id_type_eqp limit 1)
END as type_name
        from acm_bill_tbl as b 
        join acm_lost_tbl as ls on (ls.id_doc = b.id_doc)
        join eqm_equipment_tbl as eq on (eq.id = ls.id_eqp)
        join eqi_device_kinds_tbl as k on (k.id = ls.type_eqp)
        join eqk_voltage_tbl as v on (v.id = ls.id_voltage)
        where b.id_doc = $id_doc
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