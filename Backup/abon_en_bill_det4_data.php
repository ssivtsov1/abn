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

$fildsArray['val_month'] = array('f_name'=>'val_month','f_type'=>'numeric');
$fildsArray['kol_subs'] =  array('f_name'=>'kol_subs','f_type'=>'integer');
$fildsArray['ob_pay'] =    array('f_name'=>'ob_pay','f_type'=>'numeric');
$fildsArray['subs_all'] =  array('f_name'=>'subs_all','f_type'=>'numeric');
$fildsArray['subs_month'] =array('f_name'=>'subs_month','f_type'=>'numeric');
$fildsArray['norma_subskwt'] =  array('f_name'=>'norma_subskwt','f_type'=>'integer');
$fildsArray['recalc_kwt'] =array('f_name'=>'recalc_kwt','f_type'=>'numeric');
$fildsArray['recalc_subs'] =array('f_name'=>'recalc_kwt','f_type'=>'numeric');   

//$qWhere= DbBuildWhere($_POST,$fildsArray);

$id_doc = $_POST['id_doc'];
/*
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';
$qWhere=$qWhere.' id = '.$lg_id;
*/

 $SQL = "select * from (
select bs.* from acm_bill_tbl as b 
        join acm_subs_tbl as bs on (bs.id_paccnt = b.id_paccnt and bs.mmgg = b.mmgg)
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