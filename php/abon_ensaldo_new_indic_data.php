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
$total_pages =1;

$pid_paccnt = $_POST['p_id'];
$pwork_date = sql_field_val('w_date','date');
$pid_indic = sql_field_val('ind_id','int');
$pmode = sql_field_val('mode','int');

$flock  = 0;
$fedit  = 0;

$SQL_new = "select -z.id as id, mp.id_paccnt, mp.id as id_meter,mp.id_type_meter,
     ii.id as id_p_indic, mp.num_meter, m.name as type_meter, 
     mp.carry, mp.coef_comp as k_tr, z.id as id_metzone, z.id_zone, 
     ii.value as p_indic,
     ii.dat_ind as dt_p_indic, fun_mmgg() as mmgg, 
     null as indic, 
     null as indic_real, 
     null as dt_indic,
     null as id_operation,
     null as demand, 
     0 as flock
     from 
       clm_meterpoint_h as mp 
       join (select id, max(dt_b) as dt from clm_meterpoint_h  where dt_b <= $pwork_date 
           and coalesce(dt_e,$pwork_date) >= $pwork_date group by id order by id) as mp2 
           on (mp.id = mp2.id and mp2.dt = mp.dt_b)
    join clm_meter_zone_h as z on (z.id_meter = mp.id)
    left join eqi_meter_tbl as m on (m.id = mp.id_type_meter)    
    left join (select i.id, i.id_paccnt,i.id_meter,i.id_zone,i.id_energy as kind_energy,i.dat_ind, i.value
                 from acm_indication_tbl as i 
                join (select id_paccnt,id_meter,id_zone,id_energy,max(dat_ind) as dat_ind
                     from acm_indication_tbl 
                     where id_paccnt  = $pid_paccnt 
                     and dat_ind <$pwork_date
                     group by id_paccnt,id_meter,id_zone,id_energy )as i2 
                using (id_paccnt,id_meter,id_zone,id_energy,dat_ind)
    ) as ii on (ii.id_meter = mp.id  and ii.id_zone = z.id_zone and ii.kind_energy = z.kind_energy) 
    where z.dt_b <= $pwork_date and coalesce(z.dt_e,$pwork_date) >= $pwork_date
    and mp.id_paccnt = $pid_paccnt 
    order by mp.num_meter, z.id_zone;";

if ($pmode==0)
{    
     $SQL = $SQL_new;
     $fedit  = 0;
}     

if ($pmode==1)
{
  $fedit  = 1;  
  $SQL = " select w.id , w.id_paccnt, w.id_meter,w.id_typemet as id_type_meter,
    w.id_prev as id_p_indic, w.num_eqp as num_meter, m.name as type_meter, 
    w.carry, w.coef_comp as k_tr, w.id_zone, 
    i.value as p_indic,
    i.dat_ind as dt_p_indic, w.mmgg,
    w.value::int as indic, w.dat_ind as dt_indic,
    w.id_operation ,
    w.value_cons as demand, w.flock, w.indic_real
    from acm_indication_tbl as w 
    left join eqi_meter_tbl as m on (m.id = w.id_typemet)
    join clm_meterpoint_h as mp on (mp.id = w.id_meter)
       join (select id, max(dt_b) as dt from clm_meterpoint_h  where dt_b <= $pwork_date 
           and coalesce(dt_e,$pwork_date) >= $pwork_date group by id order by id) as mp2 
           on (mp.id = mp2.id and mp2.dt = mp.dt_b)
    left join acm_indication_tbl as i on (i.id = w.id_prev)
    where w.id = $pid_indic;";
}

if ($pmode==2)
{
  $SQL_sel = "select id from acm_indication_tbl as w 
       where w.id_paccnt = $pid_paccnt and w.dat_ind = $pwork_date";  
  $res = pg_query($Link,$SQL_sel) or die("SQL Error: " .pg_last_error($Link) );
  $rows_count = pg_num_rows($res);
  
  if ($rows_count==0)
  {
     $fedit  = 0;
     $SQL = "select -z.id as id, mp.id_paccnt, mp.id as id_meter,mp.id_type_meter,
     ii.id as id_p_indic, mp.num_meter, m.name as type_meter, 
     mp.carry, mp.coef_comp as k_tr, z.id as id_metzone, z.id_zone, 
     ii.value as p_indic,
     ii.dat_ind as dt_p_indic, fun_mmgg() as mmgg, 
     null as indic, 
     null as indic_real,
     $pwork_date as dt_indic,
     10 as id_operation,
     null as demand,
     0 as flock
     from 
       clm_meterpoint_h as mp 
       join (select id, max(dt_b) as dt from clm_meterpoint_h  where dt_b <= $pwork_date 
           and coalesce(dt_e,$pwork_date) >= $pwork_date group by id order by id) as mp2 
           on (mp.id = mp2.id and mp2.dt = mp.dt_b)
    join clm_meter_zone_h as z on (z.id_meter = mp.id)
    left join eqi_meter_tbl as m on (m.id = mp.id_type_meter)    
    left join (select i.id, i.id_paccnt,i.id_meter,i.id_zone,i.id_energy as kind_energy,i.dat_ind, i.value
                 from acm_indication_tbl as i 
                join (select id_paccnt,id_meter,id_zone,id_energy,max(dat_ind) as dat_ind
                     from acm_indication_tbl 
                     where id_paccnt  = $pid_paccnt 
                     and dat_ind <$pwork_date
                     group by id_paccnt,id_meter,id_zone,id_energy )as i2 
                using (id_paccnt,id_meter,id_zone,id_energy,dat_ind)
    ) as ii on (ii.id_meter = mp.id  and ii.id_zone = z.id_zone and ii.kind_energy = z.kind_energy) 
    where z.dt_b <= $pwork_date and coalesce(z.dt_e,$pwork_date) >= $pwork_date
    and mp.id_paccnt = $pid_paccnt 
    order by mp.num_meter, z.id_zone;";      
  }
  else
  {
    $fedit  = 1;  
    $SQL = " select w.id , w.id_paccnt, w.id_meter,w.id_typemet as id_type_meter,
    w.id_prev as id_p_indic, w.num_eqp as num_meter, m.name as type_meter, 
    w.carry, w.coef_comp as k_tr, w.id_zone, 
    i.value as p_indic,
    i.dat_ind as dt_p_indic, w.mmgg,
    w.value as indic, w.dat_ind as dt_indic,
    w.id_operation ,
    w.value_cons as demand, w.flock, w.indic_real
    from acm_indication_tbl as w 
    left join eqi_meter_tbl as m on (m.id = w.id_typemet)
    join clm_meterpoint_h as mp on (mp.id = w.id_meter)
       join (select id, max(dt_b) as dt from clm_meterpoint_h  where dt_b <= $pwork_date 
           and coalesce(dt_e,$pwork_date) >= $pwork_date group by id order by id) as mp2 
           on (mp.id = mp2.id and mp2.dt = mp.dt_b)
    left join acm_indication_tbl as i on (i.id = w.id_prev)
    where w.id_paccnt = $pid_paccnt and w.dat_ind = $pwork_date ;";
  }
}

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );

 if ($result) 
 {
  $data['page'] = $page;
  $data['total'] = $total_pages;

  $i = 0; 
 while($row = pg_fetch_array($result)) {

    $data['rows'][$i]['id'] = $row['id'];
    $data['rows'][$i]['id_paccnt'] = $row['id_paccnt'];
    $data['rows'][$i]['id_meter'] = $row['id_meter'];
    $data['rows'][$i]['id_type_meter'] = $row['id_type_meter'];
    $data['rows'][$i]['id_p_indic'] = $row['id_p_indic'];
    $data['rows'][$i]['num_meter'] = $row['num_meter'];
    $data['rows'][$i]['type_meter'] = $row['type_meter'];
    $data['rows'][$i]['carry'] = $row['carry'];
    $data['rows'][$i]['k_tr'] = $row['k_tr'];
    //$data['rows'][$i]['id_metzone'] = $row['id_metzone'];
    $data['rows'][$i]['id_zone'] = $row['id_zone'];
    $data['rows'][$i]['p_indic'] = $row['p_indic'];
    $data['rows'][$i]['dt_p_indic'] = $row['dt_p_indic'];
    $data['rows'][$i]['indic'] = $row['indic'];
    $data['rows'][$i]['demand'] = $row['demand'];
    $data['rows'][$i]['dt_indic'] = $row['dt_indic'];
    $data['rows'][$i]['id_operation'] = $row['id_operation'];
    $data['rows'][$i]['mmgg'] = $row['mmgg'];
    $data['rows'][$i]['indic_real'] = $row['indic_real'];

    $i++;
    if ($row['flock']==1)
    {
      $flock  = 1;  
    }
 } 
  $data['records'] = $i; 
  $data['userdata']['flock'] = $flock;  
  $data['userdata']['fedit'] = $fedit;
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>