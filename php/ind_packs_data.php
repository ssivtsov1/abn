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

 $table_name = 'ind_pack_header';

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

$fildsArray =DbGetFieldsArray($Link,$table_name);
$fildsArray['sector'] =   array('f_name'=>'sector','f_type'=>'character varying');
$fildsArray['code'] =   array('f_name'=>'code','f_type'=>'int');
$fildsArray['runner'] =   array('f_name'=>'runner','f_type'=>'character varying');
$fildsArray['operator'] =   array('f_name'=>'operator','f_type'=>'character varying');
$fildsArray['user_name'] =   array('f_name'=>'user_name','f_type'=>'character varying');

$flag_cek = is_cek($Link);  //принадлежность РЭСа к ЦЭК


if($flag_cek==1){
    // Массив исключений по типам данных
    $fieldsExeption = ['num_pack'];
    $qWhere= DbBuildWhere($_POST,$fildsArray,0,$fieldsExeption);
    }
else
    $qWhere= DbBuildWhere($_POST,$fildsArray);

$p_mmgg = sql_field_val('p_mmgg', 'date');
$year_n = substr($p_mmgg,1,4);
$month_n = substr($p_mmgg,6,2);

if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere." work_period = date_trunc('month', {$p_mmgg}::date) ";

    $Query="SELECT COUNT(*) AS count FROM 
(select t.* ,s.code,s.name as sector, p.represent_name as runner,u1.name as user_name,
        p2.represent_name as operator
        from $table_name as t 
        join prs_runner_sectors as s on (s.id = t.id_sector)
        left join prs_persons as p on (p.id = t.id_runner)
        left join prs_persons as p2 on (p2.id = s.id_operator)
        left join syi_user as u1 on (u1.id = t.id_person)
        left join act_plan_cache_tbl as p4 on (trim(s.name) = trim(p4.sector) and p4.year=$year_n and p4.month=$month_n)
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
//substring(s.name from 1 for CASE WHEN position(' ' in s.name)>0 THEN position(' ' in s.name)-1 ELSE length(s.name) END) as sector
//
   // CASE WHEN p2.represent_name is not null THEN p2.represent_name ELSE p4.cntr END as operator,

   $SQL = "select * from (select t.id_pack,t.id_sector,t.id_runner,t.dt_pack,t.id_ioper,t.id_dep,
    t.work_period,t.dt_input,t.id_person,t.flock,
    s.name as sector, s.code,
    CASE WHEN p.represent_name is not null THEN p.represent_name ELSE p4.cntr END as runner,
    p2.represent_name as operator,
    u1.name as user_name,
    t.num_pack,
        CASE WHEN bb.cnt >0 THEN 'Так' ELSE ' ' END as fcalc
        from $table_name as t 
        join prs_runner_sectors as s on (s.id = t.id_sector)
        left join prs_persons as p on (p.id = t.id_runner)
        left join prs_persons as p2 on (p2.id = s.id_operator)
        left join syi_user as u1 on (u1.id = t.id_person)
        left join act_plan_cache_tbl as p4 on (trim(s.name) = trim(p4.sector) and p4.year=$year_n and p4.month=$month_n)
        left join (
         select id_pack, count(*) as cnt  from ind_pack_data as pd 
          join acm_bill_tbl as b on (b.id_paccnt = pd.id_paccnt)
          where b.mmgg = $p_mmgg and b.id_pref = 10  
          group by id_pack
         ) as bb on (bb.id_pack = t.id_pack)
        ) as ss    
          $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";



//throw new Exception(json_encode($fildsArray));
   //cast(CASE WHEN trim(t.num_pack)<>'' THEN t.num_pack ELSE '0' END as int) as num_pack,
$f=fopen('aaa','w+');
fputs($f,$SQL);


$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

 
  $i = 0; 
 while($row = pg_fetch_array($result)) {
      $data['rows'][$i]['cell'][] = $row['dt_pack'];     
      $data['rows'][$i]['cell'][] = $row['num_pack'];           
      $data['rows'][$i]['cell'][] = $row['id_sector'];
      $data['rows'][$i]['cell'][] = $row['code'];
      $data['rows'][$i]['cell'][] = $row['sector'];      
      $data['rows'][$i]['cell'][] = $row['id_runner'];
      $data['rows'][$i]['cell'][] = $row['runner'];
      $data['rows'][$i]['cell'][] = $row['id_operator'];
      $data['rows'][$i]['cell'][] = $row['operator'];
      $data['rows'][$i]['cell'][] = $row['id_ioper'];
      $data['rows'][$i]['cell'][] = $row['work_period'];                  
      $data['rows'][$i]['cell'][] = $row['dt_input'];                  
      $data['rows'][$i]['cell'][] = $row['user_name'];                  
      $data['rows'][$i]['cell'][] = $row['fcalc'];
      $data['rows'][$i]['cell'][] = $row['id_pack'];
      
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>
