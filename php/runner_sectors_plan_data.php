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

$Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$p_mmgg = $row['mmgg'];
$year_n = substr($p_mmgg,6,4);
$month_n = substr($p_mmgg,3,2);

$page = $_POST['page']; // get the requested page
$limit = $_POST['rows']; // get how many rows we want to have into the grid
$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
$sord = $_POST['sord']; // get the direction

 // get how many rows we want to have into the grid
$table_name = 'prs_runner_sectors';

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

$fildsArray =DbGetFieldsArray($Link,$table_name);
$fildsArray['runner'] =   array('f_name'=>'runner','f_type'=>'character varying');
$fildsArray['operator'] =   array('f_name'=>'operator','f_type'=>'character varying');
$fildsArray['controler'] =   array('f_name'=>'controler','f_type'=>'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$Query="SELECT COUNT(*) AS count FROM (select t.id, t.name,t.code, t.id_runner,t.sort_flag,t.id_region,t.zone,
t.notes,t.id_dep,t.dt_b,t.work_period, t.dt_input,t.id_person,
p4.cntr as runner, p2.represent_name as operator, p3.represent_name as controler
        from $table_name as t 
        left join prs_persons as p on (p.id = t.id_runner)
        left join act_plan_cache_tbl as p4 on (t.name = p4.sector and p4.year=$year_n and p4.month=$month_n)
        left join prs_persons as p2 on (p2.id = t.id_operator)
        left join prs_persons as p3 on (p3.id = t.id_kontrol)
) as sss $qWhere;";
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

$SQL = "select * from (select t.id, t.name,t.code, t.id_runner, t.id_region, t.zone,
t.notes,t.id_dep,t.dt_b,t.work_period, t.dt_input,t.id_person, t.sort_flag,
p4.cntr as runner, p2.represent_name as operator, p3.represent_name as controler, 
u1.name as user_name 
        from $table_name as t 
        left join prs_persons as p on (p.id = t.id_runner)
        left join act_plan_cache_tbl as p4 on (trim(t.name) = trim(p4.sector) and p4.year=$year_n and p4.month=$month_n)
        left join prs_persons as p2 on (p2.id = t.id_operator)
        left join prs_persons as p3 on (p3.id = t.id_kontrol)
        left join syi_user as u1 on (u1.id = t.id_person)) as ss
          $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";



//throw new Exception(json_encode($SQL));

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {
      $data['rows'][$i]['cell'][] = $row['id'];
      $data['rows'][$i]['cell'][] = $row['code'];
      $data['rows'][$i]['cell'][] = $row['name'];
      $data['rows'][$i]['cell'][] = $row['id_runner'];
      $data['rows'][$i]['cell'][] = $row['runner'];
      $data['rows'][$i]['cell'][] = $row['operator'];      
      $data['rows'][$i]['cell'][] = $row['id_kontrol'];
      $data['rows'][$i]['cell'][] = $row['controler'];
      $data['rows'][$i]['cell'][] = $row['id_region'];      
      $data['rows'][$i]['cell'][] = $row['zone'];      
      $data['rows'][$i]['cell'][] = $row['notes'];     
      $data['rows'][$i]['cell'][] = $row['dt_b'];     
      $data['rows'][$i]['cell'][] = $row['work_period'];                  
      $data['rows'][$i]['cell'][] = $row['dt_input'];                  
      $data['rows'][$i]['cell'][] = $row['user_name']; 
      $data['rows'][$i]['cell'][] = $row['sort_flag']; 
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>