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

 $table_name = 'acd_cabindication_bad_tbl';
 

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;
 
$fildsArray =DbGetFieldsArray($Link,$table_name);
$fildsArray['address']= array('f_name'=>'address','f_type'=>'character varying');
$fildsArray['abon'] =   array('f_name'=>'abon','f_type'=>'character varying');


$qWhere= DbBuildWhere($_POST,$fildsArray);

$p_mmgg = sql_field_val('p_mmgg', 'date');

if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere." date_trunc('month', period ) = date_trunc('month', {$p_mmgg}::date) ";


$Query="SELECT COUNT(*) AS count FROM 
(
 select pd.*,  
 adr.street||' '||address_print(acc.addr) as address, 
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon
 from
  acd_cabindication_bad_tbl as pd 
  left join clm_paccnt_tbl as acc on (acc.id = pd.id_paccnt)
  left join clm_abon_tbl as c on (c.id = acc.id_abon) 
  left join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
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

$SQL = "select * from (select pd.*, acc.book, acc.code, 
  adr.street||' '||address_print(acc.addr) as address, 
  (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon
  from acd_cabindication_bad_tbl as pd 
  left join clm_paccnt_tbl as acc on (acc.id = pd.id_paccnt)
  left join clm_abon_tbl as c on (c.id = acc.id_abon) 
  left join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
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
      $data['rows'][$i]['cell'][] = $row['id_paccnt'];
      
      $data['rows'][$i]['cell'][] = $row['lic'];
      $data['rows'][$i]['cell'][] = $row['address'];
      $data['rows'][$i]['cell'][] = $row['abon'];
      
      $data['rows'][$i]['cell'][] = $row['meter_num'];
      $data['rows'][$i]['cell'][] = $row['id_zone'];
      $data['rows'][$i]['cell'][] = $row['dt_ind'];
      $data['rows'][$i]['cell'][] = $row['curr_ind'];
      $data['rows'][$i]['cell'][] = $row['curr_dt'];
      $data['rows'][$i]['cell'][] = $row['id_operation'];
      
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>