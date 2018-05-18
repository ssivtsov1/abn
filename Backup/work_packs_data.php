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

 $table_name = 'clm_work_pack_header_tbl';

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

$fildsArray =DbGetFieldsArray($Link,$table_name);
$fildsArray['sector'] =   array('f_name'=>'sector','f_type'=>'character varying');
$fildsArray['addr'] =   array('f_name'=>'addr','f_type'=>'character varying');
$fildsArray['position'] =   array('f_name'=>'position','f_type'=>'character varying');
$fildsArray['user_name'] =   array('f_name'=>'user_name','f_type'=>'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$p_mmgg = sql_field_val('p_mmgg', 'date');

if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere." work_period = date_trunc('month', {$p_mmgg}::date) ";


$Query="SELECT COUNT(*) AS count FROM 
(select t.* ,s.code,s.name as sector, p.represent_name as position,u1.name as user_name,
  (adr.town||' '||coalesce(adr.street,'')||' '||
     (coalesce('буд.'||(t.addr).house||'','')||
		coalesce('/'||(t.addr).slash||' ','')|| 
			coalesce(' корп.'||(t.addr).korp||'',''))::varchar
  )::varchar as addr
        from $table_name as t 
        left join prs_runner_sectors as s on (s.id = t.id_sector)
        left join prs_persons as p on (p.id = t.id_position)
        left join adt_addr_town_street_tbl as adr on (adr.id = (t.addr).id_class)         
        left join syi_user as u1 on (u1.id = t.id_person)
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

$SQL = "select * from 
(select t.* ,s.code,s.name as sector, p.represent_name as position,u1.name as user_name,
  (adr.town||' '||coalesce(adr.street,'')||' '||
     (coalesce('буд.'||(t.addr).house||'','')||
		coalesce('/'||(t.addr).slash||' ','')|| 
			coalesce(' корп.'||(t.addr).korp||'',''))::varchar
  )::varchar as addr_str
        from $table_name as t 
        left join prs_runner_sectors as s on (s.id = t.id_sector)
        left join prs_persons as p on (p.id = t.id_position)
        left join adt_addr_town_street_tbl as adr on (adr.id = (t.addr).id_class)         
        left join syi_user as u1 on (u1.id = t.id_person)
        ) as ss     
          $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";

//throw new Exception(json_encode($fildsArray));

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;
 
  $i = 0; 
 while($row = pg_fetch_array($result)) {
      $data['rows'][$i]['cell'][] = $row['id']; 
      $data['rows'][$i]['cell'][] = $row['dt_work'];     
      $data['rows'][$i]['cell'][] = $row['idk_work'];           
      $data['rows'][$i]['cell'][] = $row['id_position'];
      $data['rows'][$i]['cell'][] = $row['position'];
      $data['rows'][$i]['cell'][] = $row['id_sector'];            
      $data['rows'][$i]['cell'][] = $row['sector'];      
      
      $data['rows'][$i]['cell'][] = $row['book'];
      $data['rows'][$i]['cell'][] = $row['addr'];
      $data['rows'][$i]['cell'][] = $row['addr_str'];
      $data['rows'][$i]['cell'][] = $row['note'];
      $data['rows'][$i]['cell'][] = $row['work_period'];
      $data['rows'][$i]['cell'][] = $row['dt'];
      $data['rows'][$i]['cell'][] = $row['user_name'];

      
      
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>