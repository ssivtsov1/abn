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
$fildsArray =DbGetFieldsArray($Link,'clm_switching_tbl');
$fildsArray['ident'] = array('f_name' => 'ident', 'f_type' => 'int');
$fildsArray['position'] = array('f_name' => 'position', 'f_type' => 'character varying');
$fildsArray['user_name'] =   array('f_name'=>'user_name','f_type'=>'character varying');
$fildsArray['action_name'] =   array('f_name'=>'action_name','f_type'=>'character varying');
$fildsArray['switch_place'] =   array('f_name'=>'switch_place','f_type'=>'character varying');

$fildsArray['idk_work'] = array('f_name' => 'idk_work', 'f_type' => 'int');
$fildsArray['idk_reason'] = array('f_name' => 'idk_reason', 'f_type' => 'int');
$fildsArray['idk_abn_state'] = array('f_name' => 'idk_abn_state', 'f_type' => 'int');
$fildsArray['task_state'] = array('f_name' => 'task_state', 'f_type' => 'int');
$fildsArray['task_state_name'] = array('f_name' => 'task_state_name', 'f_type' => 'character varying');


$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_paccnt = $_POST['p_id'];
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' id_paccnt = '.$pid_paccnt;


$Query="SELECT COUNT(*) AS count FROM (
 select 1 as ident, s.id, s.id_paccnt, sa.name as action_name, s.sum_warning,
       u1.name as user_name , s.dt, s.mmgg, 
       s.dt_create, s.dt_action, s.act_num, s.comment, 
       sp.name as switch_place, 

       s.action, s.dt_warning, s.dt_sum, s.id_switch_place, 
       s.demand_varning, s.mmgg_debet, s.id_position, cn.represent_name as position,
    
       null as idk_work, null as idk_reason, null as idk_abn_state, null as task_state , null as  task_state_name

 from clm_switching_tbl as s 
 left join cli_switch_action_tbl as sa on (sa.id = s.action)
 left join cli_switch_place_tbl as sp on (sp.id = s.id_switch_place )
 left join prs_persons as cn on (cn.id = s.id_position)   
 left join syi_user as u1 on (u1.id = s.id_person)
 where s.id_paccnt = $pid_paccnt

 union all

 SELECT 2 as ident, t.id, t.id_paccnt, 'Завдання:'||ti.name as action_name, t.sum_warning, 
  u2.name as user_name , t.dt_input as dt, t.work_period as mmgg, 
  t.date_print, t.date_work, t.task_num, t.note,
  tr.name as reason,

  null as action, null as dt_warning, null as dt_sum, null as id_switch_place, 
  null as demand_varning, null as mmgg_debet, null as id_position, null as position,

  t.idk_work, t.idk_reason, t.idk_abn_state, t.task_state ,ts.name as  task_state_name

 FROM clm_tasks_tbl as t 
 left join cli_tasks_tbl as ti on (ti.id = t.idk_work)
 left join cli_tasks_reason_tbl as tr on (tr.id = idk_reason)
 left join cli_tasks_state_tbl as ts on (ts.id = t.task_state)
 left join syi_user as u2 on (u2.id = t.id_person)
 where t.id_paccnt = $pid_paccnt

) as ss
$qWhere;";
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
 select 1 as ident, s.id, s.id_paccnt, sa.name as action_name, s.sum_warning,
       u1.name as user_name , s.dt, s.mmgg, 
       s.dt_create, s.dt_action, s.act_num, s.comment,
       sp.name as switch_place, 

       s.action, s.dt_warning, s.dt_sum, s.id_switch_place, 
       s.demand_varning, s.mmgg_debet, s.id_position, cn.represent_name as position,
    
       null as idk_work, null as idk_reason, null as idk_abn_state, null as task_state , null as  task_state_name

 from clm_switching_tbl as s 
 left join cli_switch_action_tbl as sa on (sa.id = s.action)
 left join cli_switch_place_tbl as sp on (sp.id = s.id_switch_place )
 left join prs_persons as cn on (cn.id = s.id_position)   
 left join syi_user as u1 on (u1.id = s.id_person)
 where s.id_paccnt = $pid_paccnt

 union all

 SELECT 2 as ident, t.id, t.id_paccnt, 'Завдання:'||ti.name as action_name, t.sum_warning, 
  u2.name as user_name , t.dt_input as dt, t.work_period as mmgg, 
  t.date_print, t.date_work, t.task_num, t.note,
  tr.name as reason,

  null as action, null as dt_warning, null as dt_sum, null as id_switch_place, 
  null as demand_varning, null as mmgg_debet, null as id_position, null as position,

  t.idk_work, t.idk_reason, t.idk_abn_state, t.task_state ,ts.name as  task_state_name

 FROM clm_tasks_tbl as t 
 left join cli_tasks_tbl as ti on (ti.id = t.idk_work)
 left join cli_tasks_reason_tbl as tr on (tr.id = idk_reason)
 left join cli_tasks_state_tbl as ts on (ts.id = t.task_state)
 left join syi_user as u2 on (u2.id = t.id_person)
 where t.id_paccnt = $pid_paccnt

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