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
      $sidx="mmgg $sord, dat_ind $sord ,id_operation ASC";
    else
      $sidx="mmgg $sord, dat_ind $sord ,id_operation DESC";        
    $sord='';
}

// connect to the database
$fildsArray =DbGetFieldsArray($Link,'acm_indication_tbl');
$fildsArray['type_meter'] = array('f_name' => 'type_meter', 'f_type' => 'character varying');
$fildsArray['is_manual'] = array('f_name' => 'is_manual', 'f_type' => 'integer');
$fildsArray['p_indic'] = array('f_name' => 'p_indic', 'f_type' => 'integer');
$fildsArray['p_dat_ind'] = array('f_name' => 'p_dat_ind', 'f_type' => 'date');
$fildsArray['num_pack'] = array('f_name' => 'num_pack', 'f_type' => 'character varying');
$fildsArray['id_hwork'] = array('f_name' => 'id_hwork', 'f_type' => 'integer');
$fildsArray['idk_work'] = array('f_name' => 'idk_work', 'f_type' => 'integer');
//$fildsArray['indic_real'] = array('f_name' => 'indic_real', 'f_type' => 'integer');
$fildsArray['user_name'] =   array('f_name'=>'user_name','f_type'=>'character varying');
$fildsArray['is_corrected'] = array('f_name' => 'is_corrected', 'f_type' => 'integer');
$fildsArray['is_last'] = array('f_name' => 'is_last', 'f_type' => 'integer');
$fildsArray['ind_mode'] = array('f_name' => 'ind_mode', 'f_type' => 'integer');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_paccnt = $_POST['p_id'];
/*
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';
$qWhere=$qWhere.' id_paccnt = '.$pid_paccnt;
*/

$Query="SELECT COUNT(*) AS count FROM (
 select n.id, n.dt, n.id_person, n.id_paccnt, n.dat_ind, n.id_meter, n.id_typemet, 
        n.carry, n.coef_comp, n.id_zone, n.num_eqp, n.id_energy, n.id_operation, 
        n.value, n.id_prev, n.value_diff, n.value_cons, n.id_ind, n.id_work, n.mmgg, 
        n.flock, n.id_corr, n.mmgg_corr, n.indic_real,
 m.name as type_meter,
 CASE WHEN id_ind is null and id_work is null THEN 1 ELSE 0 END as is_manual,
 u1.name as user_name ,
 case WHEN exists (select c.id from acm_indication_h as c where c.id_paccnt = $pid_paccnt and c.id = n.id and c.mmgg_change <> n.mmgg ) THEN 1 ELSE 0 END as is_corrected,
 1 as ind_mode
 from acm_indication_tbl as n
 left join eqi_meter_tbl as m on (m.id = n.id_typemet)
 left join syi_user as u1 on (u1.id = n.id_person)
 where n.id_paccnt = $pid_paccnt
union all
 select pd.id, pd.dt_input as dt, pd.id_person, pd.id_paccnt, coalesce(pd.dt_indic,ph.dt_pack,pd.work_period) as dat_ind,
        pd.id_meter, pd.id_type_meter as id_typemet, 
        pd.carry, pd.k_tr as coef_comp, pd.id_zone, pd. num_meter as num_eqp, pd.kind_energy as id_energy, pd.id_operation, 
        pd.indic as value, null as id_prev, null as value_diff, null as value_cons, null as id_ind, null as id_work, pd.work_period as mmgg, 
        pd.flock, null as id_corr, null as mmgg_corr, null as indic_real,
 m.name as type_meter,
 0 as is_manual,
 u1.name as user_name ,
 0 as is_corrected,
 2 as ind_mode
  from 
   ind_pack_data as pd 
   join ind_pack_header as ph on (pd.id_pack = ph.id_pack)
   left join eqi_meter_tbl as m on (m.id = pd.id_type_meter)
   left join syi_user as u1 on (u1.id = pd.id_person)
   where id_operation is not null
   and id_operation in (16,26)
   and id_indic is null
   and pd.id_paccnt = $pid_paccnt
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
 select n.id, n.dt, n.id_person, n.id_paccnt, n.dat_ind, n.id_meter, n.id_typemet, 
        n.carry, n.coef_comp, n.id_zone, n.num_eqp, n.id_energy, n.id_operation, 
        n.value, n.id_prev, n.value_diff, n.value_cons, n.id_ind, n.id_work, n.mmgg, 
        n.flock, n.id_corr, n.mmgg_corr, n.indic_real,
 m.name as type_meter,i2.value as p_indic,i2.dat_ind as p_dat_ind,
 CASE WHEN n.id_ind is null and n.id_work is null THEN 1 ELSE 0 END as is_manual,
 ph.num_pack, ww.id_work as id_hwork, w.idk_work, 
 coalesce(n.indic_real,pd.indic_real::int) as indic_real,
 case WHEN exists (select c.id from acm_indication_h as c where c.id_paccnt = $pid_paccnt and c.id = n.id and c.mmgg_change <>n.mmgg ) THEN 1 ELSE 0 END as is_corrected,
 case WHEN not exists (select c.id from acm_indication_tbl as c where c.id_paccnt = $pid_paccnt and c.mmgg =n.mmgg and c.id_zone = n.id_zone and c.dat_ind > n.dat_ind) THEN 1 ELSE 0 END as is_last,
 u1.name as user_name , 1 as ind_mode
 from acm_indication_tbl as n
 left join eqi_meter_tbl as m on (m.id = n.id_typemet)
 left join acm_indication_tbl as i2 on (i2.id = n.id_prev)
 left join ind_pack_data as pd on (n.id_ind = pd.id)
 left join ind_pack_header as ph on (pd.id_pack = ph.id_pack)
 left join clm_work_indications_tbl as ww on (ww.id = n.id_work)
 left join clm_works_tbl as w on (w.id = ww.id_work)
 left join syi_user as u1 on (u1.id = n.id_person)
 where n.id_paccnt = $pid_paccnt

union all

 select pd.id, pd.dt_input as dt, pd.id_person, pd.id_paccnt, coalesce(pd.dt_indic,ph.dt_pack,pd.work_period) as dat_ind,
        pd.id_meter, pd.id_type_meter as id_typemet, 
        pd.carry, pd.k_tr as coef_comp, pd.id_zone, pd. num_meter as num_eqp, pd.kind_energy as id_energy, pd.id_operation, 
        pd.indic as value, null as id_prev, null as value_diff, null as value_cons, null as id_ind, null as id_work, pd.work_period as mmgg, 
        pd.flock, null as id_corr, null as mmgg_corr, null as indic_real,
 m.name as type_meter,null as p_indic,null as p_dat_ind,
 0 as is_manual,
 ph.num_pack, null as id_hwork, null as idk_work, 
 null as indic_real,
 0 as is_corrected,
 0 as is_last,
 u1.name as user_name , 2 as ind_mode
  from 
   ind_pack_data as pd 
   join ind_pack_header as ph on (pd.id_pack = ph.id_pack)
   left join eqi_meter_tbl as m on (m.id = pd.id_type_meter)
   left join syi_user as u1 on (u1.id = pd.id_person)
   where id_operation is not null
   and id_operation in (16,26)
   and id_indic is null
   and pd.id_paccnt = $pid_paccnt
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