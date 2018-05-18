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

$fildsArray['alt_code'] =   array('f_name'=>'alt_code','f_type'=>'character varying');
$fildsArray['name_lgt'] =   array('f_name'=>'name_lgt','f_type'=>'character varying');
$fildsArray['fio_lgt'] =   array('f_name'=>'fio_lgt','f_type'=>'character varying');
$fildsArray['tar_grp'] =   array('f_name'=>'tar_grp','f_type'=>'character varying');

$fildsArray['norm_min'] =   array('f_name'=>'norm_min','f_type'=>'numeric');
$fildsArray['norm_one'] =   array('f_name'=>'norm_one','f_type'=>'numeric');
$fildsArray['norm_max'] =   array('f_name'=>'norm_max','f_type'=>'numeric');
$fildsArray['percent'] =   array('f_name'=>'percent','f_type'=>'numeric');

$fildsArray['famyly_cnt'] =   array('f_name'=>'famyly_cnt','f_type'=>'integer');

$fildsArray['norm_abon'] =   array('f_name'=>'norm_abon','f_type'=>'numeric');
$fildsArray['norm_abon_heat'] =   array('f_name'=>'norm_abon_heat','f_type'=>'numeric');
$fildsArray['norm_lgt'] =   array('f_name'=>'norm_lgt','f_type'=>'numeric');
$fildsArray['norm_add_lgt'] =   array('f_name'=>'norm_add_lgt','f_type'=>'numeric');

$fildsArray['dt_beg'] =   array('f_name'=>'dt_beg','f_type'=>'date');
$fildsArray['dt_fin'] =   array('f_name'=>'dt_fin','f_type'=>'date');

//$qWhere= DbBuildWhere($_POST,$fildsArray);

$id_doc = $_POST['id_doc'];
/*
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' id = '.$lg_id;
*/

 $SQL = "select * from (
select bs.norm_min, bs.norm_one,bs.norm_max,bs.famyly_cnt,bs.percent, bs.norm_abon, bs.norm_abon_heat, 
bs.norm_lgt,bs.norm_add_lgt, bs.id_paccnt, lg.name as name_lgt, lg.alt_code, bs.dt_beg, bs.dt_fin,
lt.name as tar_grp,lh.fio_lgt
        from acm_bill_tbl as b 
        join acm_lgt_tbl as bs on (bs.id_doc = b.id_doc)
        join lgi_group_h as lg on (lg.id = bs.id_grp_lgt)
        join lgi_norm_tbl as ln on (ln.id = bs.id_ln) 
        join lgi_tar_grp_tbl as lt on (lt.id = bs.id_tar_grp)
        left join lgm_abon_tbl as lh on (lh.id = bs.id_lgm)
        where b.id_doc = $id_doc 
	and lg.dt_b = (select max(dt_b) from lgi_group_h  where id = lg.id and dt_b <= b.reg_date and coalesce(dt_e,b.reg_date) >=b.reg_date)
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