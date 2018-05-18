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

 $table_name = 'ind_smart_indics_tbl';

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

if ($sidx=='tu')  $sidx="int_tu $sord ,tu";
if ($sidx=='code')  $sidx="int_code $sord ,code";

$fildsArray =DbGetFieldsArray($Link,$table_name);
$fildsArray['book'] =   array('f_name'=>'book','f_type'=>'character varying');
$fildsArray['code'] =   array('f_name'=>'code','f_type'=>'character varying');
//$fildsArray['bookcode'] =   array('f_name'=>'bookcode','f_type'=>'character varying');
$fildsArray['address'] =   array('f_name'=>'address','f_type'=>'character varying');
$fildsArray['is_load'] =   array('f_name'=>'is_load','f_type'=>'int');
$fildsArray['p_indic'] =   array('f_name'=>'p_indic','f_type'=>'int');
$fildsArray['period_flag'] =   array('f_name'=>'period_flag','f_type'=>'int');


$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_header = $_POST['p_id'];
if ($qWhere!='') $qWhere=$qWhere.' and '; 
else $qWhere=' where ';
$qWhere=$qWhere.' id_smart = '.$pid_header;



$Query="SELECT COUNT(*) AS count FROM 
(select i.* ,address_print(acc.addr) as address, acc.book||'/'||acc.code as bookcode,acc.book,acc.code,
        CASE WHEN i.id_indic is null THEN 0 else 1 END as is_load,
        CASE WHEN acc.id is null then 2 WHEN date_trunc('month', i.ind_date) = i.mmgg  THEN 0 ELSE 1 END as period_flag,
        ii.value as p_indic
        from ind_smart_indics_tbl as i
        left join clm_paccnt_tbl as acc on (acc.id = i.id_paccnt)
        left join ind_pack_data as pd on (pd.id = i.id_indic)
        left join acm_indication_tbl as ii on (ii.id = pd.id_p_indic)
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
       select i.* ,address_print(acc.addr) as address, acc.book||'/'||acc.code as bookcode,acc.book,acc.code,
       CASE WHEN acc.id is null then 2 WHEN date_trunc('month', i.ind_date) = i.mmgg  THEN 0 ELSE 1 END as period_flag,
       ('0'||substring(i.tu FROM '[0-9]+'))::int as int_tu,
       ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
       CASE WHEN i.id_indic is null THEN 0 else 1 END as is_load,
       ii.value as p_indic
        from ind_smart_indics_tbl as i
        left join clm_paccnt_tbl as acc on (acc.id = i.id_paccnt)
        left join ind_pack_data as pd on (pd.id = i.id_indic)
        left join acm_indication_tbl as ii on (ii.id = pd.id_p_indic)
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
      $data['rows'][$i]['cell'][] = $row['id_smart'];           
      $data['rows'][$i]['cell'][] = $row['id_indic'];      
      $data['rows'][$i]['cell'][] = $row['id_paccnt'];
      //$data['rows'][$i]['cell'][] = $row['id_meter'];
      
      $data['rows'][$i]['cell'][] = $row['tu'];
      $data['rows'][$i]['cell'][] = $row['ind_a'];
      $data['rows'][$i]['cell'][] = $row['ind_b'];
      $data['rows'][$i]['cell'][] = $row['ind_c'];                  
      $data['rows'][$i]['cell'][] = $row['ind_d'];                  
      $data['rows'][$i]['cell'][] = $row['indic'];                  
      $data['rows'][$i]['cell'][] = $row['p_indic'];                  
      $data['rows'][$i]['cell'][] = $row['book'];
      $data['rows'][$i]['cell'][] = $row['code'];
      //$data['rows'][$i]['cell'][] = $row['bookcode'];      
      $data['rows'][$i]['cell'][] = $row['address'];
      $data['rows'][$i]['cell'][] = $row['ind_date'];
      $data['rows'][$i]['cell'][] = $row['is_load'];
      $data['rows'][$i]['cell'][] = $row['period_flag'];
      
      
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>