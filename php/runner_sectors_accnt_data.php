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
$table_name = 'prs_runner_paccnt';

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

if ($sidx=='code')  $sidx="book $sord ,int_code $sord ,code";
if ($sidx=='book')  $sidx="book $sord ,int_code $sord, code";

$fildsArray =DbGetFieldsArray($Link,$table_name);
$fildsArray['book'] =   array('f_name'=>'book','f_type'=>'char');
$fildsArray['code'] =   array('f_name'=>'code','f_type'=>'char');
$fildsArray['abon'] =   array('f_name'=>'abon','f_type'=>'character varying');
$fildsArray['address'] =   array('f_name'=>'address','f_type'=>'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_eqp = $_POST['p_id'];
if ($qWhere!='') $qWhere=$qWhere.' and '; 
else $qWhere=' where ';
$qWhere=$qWhere.' id_sector = '.$pid_eqp;


$Query="SELECT COUNT(*) AS count FROM (select t.* ,
       acc.book, acc.code, adr.adr||' '||address_print(acc.addr) as address,
       (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon
        from $table_name as t 
        join clm_paccnt_tbl as acc on (acc.id = t.id_paccnt)
        join clm_abon_tbl as c on (c.id = acc.id_abon) 
        join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)) as ss $qWhere;";
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

$SQL = " select * from (select t.* ,
       acc.book, acc.code, 
       regexp_replace(regexp_replace(acc.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
       adr.adr||' '||address_print(acc.addr) as address, 
       u1.name as username ,
       (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon
        from $table_name as t 
        join clm_paccnt_tbl as acc on (acc.id = t.id_paccnt)
        join clm_abon_tbl as c on (c.id = acc.id_abon) 
        join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
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
      $data['rows'][$i]['cell'][] = $row['id_sector'];
      $data['rows'][$i]['cell'][] = $row['id_paccnt'];
      
      $data['rows'][$i]['cell'][] = $row['book'];
      $data['rows'][$i]['cell'][] = $row['code'];
      $data['rows'][$i]['cell'][] = $row['address'];
      $data['rows'][$i]['cell'][] = $row['abon'];     
      $data['rows'][$i]['cell'][] = $row['dt_b'];     
      $data['rows'][$i]['cell'][] = $row['id_region'];     
      $data['rows'][$i]['cell'][] = $row['work_period'];                  
      $data['rows'][$i]['cell'][] = $row['dt_input'];                  
      $data['rows'][$i]['cell'][] = $row['user_name'];                  
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>