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
$table_name = 'acm_headpay_tbl';

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

$fildsArray =DbGetFieldsArray($Link,$table_name);
$fildsArray['user_name'] =   array('f_name'=>'user_name','f_type'=>'character varying');
$fildsArray['cnt_all'] = array('f_name' => 'cnt_all', 'f_type' => 'int');
$fildsArray['cnt_bad'] = array('f_name' => 'cnt_bad', 'f_type' => 'int');
$fildsArray['sum_all'] = array('f_name' => 'sum_all', 'f_type' => 'numeric');
$fildsArray['sum_bad'] = array('f_name' => 'sum_bad', 'f_type' => 'numeric');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$p_mmgg = sql_field_val('p_mmgg', 'date');

if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere." mmgg = date_trunc('month', {$p_mmgg}::date) ";

$Query="SELECT COUNT(*) AS count FROM 
(select t.* , u1.name as user_name , s.cnt_all, s.sum_all, s.cnt_bad, s.sum_bad
        from acm_headpay_tbl as t 
        join (
         select id_headpay, count(id) as cnt_all, sum(summ) as sum_all, 
         sum(CASE WHEN id_paccnt is null THEN 1 END ) as cnt_bad, 
         sum(CASE WHEN id_paccnt is null THEN summ END ) as sum_bad
         from acm_pay_load_tbl 
         group by id_headpay
        ) as s on (s.id_headpay = t.id)
        left join syi_user as u1 on (u1.id = t.id_person)
        ) as ss  $qWhere;";
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
        select t.* , u1.name as user_name , s.cnt_all, s.sum_all, s.cnt_bad, s.sum_bad, o.name as origin
        from acm_headpay_tbl as t 
        join (
         select id_headpay, count(id) as cnt_all, sum(summ) as sum_all, 
         sum(CASE WHEN id_paccnt is null THEN 1 END ) as cnt_bad, 
         sum(CASE WHEN id_paccnt is null THEN summ END ) as sum_bad
         from acm_pay_load_tbl 
         group by id_headpay
        ) as s on (s.id_headpay = t.id)
        left join aci_pay_origin_tbl as o on (o.id = t.id_origin)
        left join syi_user as u1 on (u1.id = t.id_person)
        ) as ss    
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
      $data['rows'][$i]['cell'][] = $row['reg_date'];     
      $data['rows'][$i]['cell'][] = $row['reg_num'];           
      
      $data['rows'][$i]['cell'][] = $row['id_origin'];
      $data['rows'][$i]['cell'][] = $row['origin'];
      $data['rows'][$i]['cell'][] = $row['name_file'];
      
      $data['rows'][$i]['cell'][] = $row['cnt_all'];      
      $data['rows'][$i]['cell'][] = $row['sum_all'];
      $data['rows'][$i]['cell'][] = $row['cnt_bad'];      
      $data['rows'][$i]['cell'][] = $row['sum_bad'];

      $data['rows'][$i]['cell'][] = $row['mmgg_str'];
      $data['rows'][$i]['cell'][] = $row['dt'];
      $data['rows'][$i]['cell'][] = $row['user_name'];
      $data['rows'][$i]['cell'][] = $row['flock'];
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>